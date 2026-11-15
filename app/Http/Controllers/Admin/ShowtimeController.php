<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShowtimeRequest;
use App\Mail\ShowtimeCancelledMail;
use App\Models\Movie;
use App\Models\Screen;
use App\Models\Showtime;
use App\Services\AuditLogger;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ShowtimeController extends Controller
{
    public function index(Request $request)
    {
        $showtimes = Showtime::with(['movie', 'screen'])
            ->withCount('bookings')
            ->when($request->query('movie'), fn ($q, $id) => $q->where('movie_id', $id))
            ->when($request->query('date'), fn ($q, $date) => $q->whereDate('start_time', $date))
            ->when($request->query('when') === 'upcoming', fn ($q) => $q->where('start_time', '>', now()))
            ->orderByDesc('start_time')
            ->paginate(15)
            ->withQueryString();

        $movies = Movie::orderBy('title')->get();

        return view('admin.showtimes.index', compact('showtimes', 'movies'));
    }

    public function create()
    {
        return view('admin.showtimes.form', [
            'showtime' => new Showtime(['status' => 'scheduled', 'base_price_cents' => 1000]),
            'movies' => Movie::orderBy('title')->get(),
            'screens' => Screen::orderBy('name')->get(),
        ]);
    }

    public function store(ShowtimeRequest $request)
    {
        $data = $request->validated();
        $this->guardAgainstOverlap($data);

        $showtime = Showtime::create($data);

        AuditLogger::log('showtime.created', $showtime, [], $showtime->only(['movie_id', 'screen_id', 'start_time']));

        return redirect()->route('admin.showtimes.index')->with('success', 'Showtime scheduled.');
    }

    public function edit(Showtime $showtime)
    {
        return view('admin.showtimes.form', [
            'showtime' => $showtime,
            'movies' => Movie::orderBy('title')->get(),
            'screens' => Screen::orderBy('name')->get(),
        ]);
    }

    public function update(ShowtimeRequest $request, Showtime $showtime)
    {
        if ($showtime->status === 'cancelled') {
            return back()->with('error', 'A cancelled showtime can no longer be edited.');
        }

        $data = $request->validated();

        if ($data['status'] === 'cancelled') {
            throw ValidationException::withMessages([
                'status' => 'Use the Cancel action so every affected booking is refunded.',
            ]);
        }

        $this->guardAgainstOverlap($data, $showtime);

        $before = $showtime->only(['movie_id', 'screen_id', 'start_time', 'base_price_cents', 'status']);

        $showtime->update($data);

        AuditLogger::log('showtime.updated', $showtime, $before, $showtime->only(['movie_id', 'screen_id', 'start_time', 'base_price_cents', 'status']));

        return redirect()->route('admin.showtimes.index')->with('success', 'Showtime updated.');
    }

    public function cancel(Showtime $showtime, RefundService $refunds)
    {
        if ($showtime->status === 'cancelled') {
            return back()->with('error', 'This showtime is already cancelled.');
        }

        $result = \Illuminate\Support\Facades\DB::transaction(function () use ($showtime, $refunds) {
            /** @var Showtime|null $locked */
            $locked = Showtime::lockForUpdate()->find($showtime->id);

            if (! $locked || $locked->status === 'cancelled') {
                return null;
            }

            $locked->update(['status' => 'cancelled', 'cancelled_at' => now()]);

            $bookings = $locked->bookings()
                ->with('user')
                ->whereIn('status', ['confirmed', 'pending_payment'])
                ->lockForUpdate()
                ->get();

            foreach ($bookings as $booking) {
                $refunds->cancelBooking(
                    $booking,
                    'The cinema cancelled this showtime.',
                    $booking->status === 'confirmed',
                );
            }

            $locked->bookings()
                ->where('status', 'expired')
                ->update(['status' => 'cancelled']);

            $recipients = $bookings->pluck('user')->filter()->unique('id');

            \Illuminate\Support\Facades\DB::afterCommit(function () use ($recipients, $locked) {
                foreach ($recipients as $user) {
                    Mail::to($user)->queue(new ShowtimeCancelledMail($locked));
                }
            });

            AuditLogger::log('showtime.cancelled', $locked, ['status' => 'scheduled'], ['status' => 'cancelled'], [
                'bookings' => $bookings->count(),
                'emails' => $recipients->count(),
            ]);

            return ['bookings' => $bookings->count(), 'emails' => $recipients->count()];
        });

        if (! $result) {
            return back()->with('error', 'This showtime is already cancelled.');
        }

        return back()->with('success', "Showtime cancelled. {$result['bookings']} bookings refunded and {$result['emails']} customers notified.");
    }

    public function destroy(Showtime $showtime)
    {
        if ($showtime->bookings()->exists()) {
            return back()->with('error', 'This showtime has bookings attached. Cancel the showtime instead of deleting it.');
        }

        $before = $showtime->only(['movie_id', 'screen_id', 'start_time', 'base_price_cents', 'status']);

        $showtime->delete();

        AuditLogger::log('showtime.deleted', null, $before, [], ['showtime_id' => $showtime->id]);

        return redirect()->route('admin.showtimes.index')->with('success', 'Showtime deleted.');
    }

    private function guardAgainstOverlap(array $data, ?Showtime $showtime = null): void
    {
        $start = \Illuminate\Support\Carbon::parse($data['start_time']);
        $duration = (int) Movie::whereKey($data['movie_id'])->value('duration_minutes');
        $end = $start->copy()->addMinutes($duration);

        $conflict = Showtime::with('movie')
            ->where('screen_id', $data['screen_id'])
            ->where('status', 'scheduled')
            ->when($showtime, fn ($q) => $q->whereKeyNot($showtime->id))
            ->where('start_time', '<', $end)
            ->get()
            ->first(fn (Showtime $other) => $other->start_time->copy()->addMinutes($other->movie->duration_minutes)->gt($start));

        if ($conflict) {
            throw ValidationException::withMessages([
                'start_time' => 'This screen is already booked from '
                    .$conflict->start_time->format('D j M, g:i A')
                    .' to '
                    .$conflict->start_time->copy()->addMinutes($conflict->movie->duration_minutes)->format('g:i A')
                    .' for '.$conflict->movie->title.'.',
            ]);
        }
    }
}
