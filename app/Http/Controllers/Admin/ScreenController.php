<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Screen;
use App\Models\Seat;
use App\Models\SeatHold;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ScreenController extends Controller
{
    public function index()
    {
        $screens = Screen::withCount('seats')
            ->withCount(['seats as premium_count' => fn ($q) => $q->where('is_premium', true)])
            ->withCount('showtimes')
            ->orderBy('name')
            ->get();

        return view('admin.screens.index', compact('screens'));
    }

    public function create()
    {
        return view('admin.screens.form', ['screen' => new Screen()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $screen = Screen::create(['name' => $data['name']]);
        $this->buildSeats($screen, $data['rows'], $data['seats_per_row'], $data['premium_rows']);

        AuditLogger::log('screen.created', $screen, [], $screen->only(['name', 'rows', 'seats_per_row', 'premium_rows']));

        return redirect()->route('admin.screens.edit', $screen)->with('success', 'Screen created with '.$screen->seats()->count().' seats.');
    }

    public function edit(Screen $screen)
    {
        $screen->load('seats');

        return view('admin.screens.form', compact('screen'));
    }

    public function update(Request $request, Screen $screen)
    {
        $data = $this->validated($request, $screen);

        $layoutChanged = (int) $screen->rows !== $data['rows']
            || (int) $screen->seats_per_row !== $data['seats_per_row']
            || (int) $screen->premium_rows !== $data['premium_rows'];

        if ($layoutChanged && $screen->showtimes()->exists()) {
            throw ValidationException::withMessages([
                'rows' => 'This screen already has showtimes, so its seat layout can no longer be regenerated.',
            ]);
        }

        $screen->update(['name' => $data['name']]);

        if ($layoutChanged) {
            $this->buildSeats($screen, $data['rows'], $data['seats_per_row'], $data['premium_rows']);
        }

        AuditLogger::log('screen.updated', $screen, [], $screen->only(['name', 'rows', 'seats_per_row', 'premium_rows']));

        return redirect()->route('admin.screens.edit', $screen)->with('success', 'Screen updated.');
    }

    public function destroy(Screen $screen)
    {
        if ($screen->showtimes()->exists()) {
            return back()->with('error', 'This screen has showtimes. Remove them before deleting the screen.');
        }

        $screen->seats()->delete();
        $screen->delete();

        AuditLogger::log('screen.deleted', null, ['screen' => $screen->name]);

        return redirect()->route('admin.screens.index')->with('success', 'Screen deleted.');
    }

    public function toggleSeat(Screen $screen, Seat $seat)
    {
        abort_unless($seat->screen_id === $screen->id, 404);

        if (! $seat->is_disabled && $this->seatInUse($seat)) {
            return back()->with('error', "Seat {$seat->label} has active bookings and cannot be disabled.");
        }

        $seat->update(['is_disabled' => ! $seat->is_disabled]);

        AuditLogger::log('screen.seat_toggled', $seat, ['is_disabled' => ! $seat->is_disabled], ['is_disabled' => $seat->is_disabled]);

        return back()->with('success', "Seat {$seat->label} ".($seat->is_disabled ? 'disabled' : 'enabled').'.');
    }

    private function seatInUse(Seat $seat): bool
    {
        $hasFutureBooking = DB::table('booking_seat')
            ->join('bookings', 'bookings.id', '=', 'booking_seat.booking_id')
            ->join('showtimes', 'showtimes.id', '=', 'bookings.showtime_id')
            ->where('booking_seat.seat_id', $seat->id)
            ->where('bookings.status', 'confirmed')
            ->where('showtimes.start_time', '>', now())
            ->exists();

        $hasActiveHold = SeatHold::where('seat_id', $seat->id)
            ->where('expires_at', '>', now())
            ->exists();

        return $hasFutureBooking || $hasActiveHold;
    }

    private function buildSeats(Screen $screen, int $rows, int $perRow, int $premiumRows): void
    {
        $screen->seats()->delete();

        $letters = range('A', 'Z');
        $firstPremiumIndex = max(0, $rows - $premiumRows);

        for ($r = 0; $r < $rows; $r++) {
            for ($number = 1; $number <= $perRow; $number++) {
                Seat::create([
                    'screen_id' => $screen->id,
                    'row' => $letters[$r],
                    'number' => $number,
                    'is_premium' => $r >= $firstPremiumIndex && $premiumRows > 0,
                    'is_disabled' => false,
                ]);
            }
        }

        $screen->update([
            'rows' => $rows,
            'seats_per_row' => $perRow,
            'premium_rows' => $premiumRows,
        ]);
    }

    private function validated(Request $request, ?Screen $screen = null): array
    {
        $maxRows = $screen?->rows ? max(26, (int) $screen->rows) : 26;

        return $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('screens', 'name')->ignore($screen?->id)],
            'rows' => ['required', 'integer', 'min:1', 'max:'.$maxRows],
            'seats_per_row' => ['required', 'integer', 'min:1', 'max:40'],
            'premium_rows' => ['required', 'integer', 'min:0', 'lte:rows'],
        ]);
    }
}
