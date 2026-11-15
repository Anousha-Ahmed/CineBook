<?php

namespace App\Http\Controllers;

use App\Models\Showtime;
use App\Models\Ticket;
use App\Models\TicketScan;
use App\Services\TicketScanService;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $showtimes = Showtime::with(['movie', 'screen'])
            ->whereDate('start_time', today())
            ->orderBy('start_time')
            ->get();

        $selectedId = $request->integer('showtime') ?: $showtimes->first()?->id;
        $selected = $showtimes->firstWhere('id', $selectedId) ?? $showtimes->first();

        $total = 0;
        $scanned = 0;

        if ($selected) {
            $total = Ticket::whereHas('booking', fn ($q) => $q
                ->where('showtime_id', $selected->id)
                ->where('status', 'confirmed'))
                ->count();

            $scanned = Ticket::whereHas('booking', fn ($q) => $q
                ->where('showtime_id', $selected->id)
                ->where('status', 'confirmed'))
                ->where('status', 'used')
                ->count();
        }

        $recent = TicketScan::with(['ticket.booking.showtime.movie', 'ticket.seat', 'staff'])
            ->when($selected, fn ($q) => $q->whereHas(
                'ticket.booking',
                fn ($inner) => $inner->where('showtime_id', $selected->id)
            ))
            ->latest()
            ->take(12)
            ->get();

        return view('staff.scan', compact('showtimes', 'selected', 'total', 'scanned', 'recent'));
    }

    public function verify(Request $request, TicketScanService $scanner)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:120'],
            'showtime_id' => ['nullable', 'integer', 'exists:showtimes,id'],
        ]);

        $showtimeId = $data['showtime_id'] ?? null;

        $outcome = $scanner->scan($data['token'], auth()->id(), $showtimeId ? (int) $showtimeId : null);
        $ticket = $outcome['ticket'];

        $summary = null;
        if ($ticket) {
            $summary = [
                'movie' => $ticket->booking?->showtime?->movie?->title,
                'screen' => $ticket->booking?->showtime?->screen?->name,
                'start' => $ticket->booking?->showtime?->start_time?->format('D j M, g:i A'),
                'seat' => $ticket->seat?->label,
                'booking' => '#'.$ticket->booking_id,
            ];
        }

        return redirect()
            ->route('staff.scan', $showtimeId ? ['showtime' => $showtimeId] : [])
            ->with('scan', [
                'result' => $outcome['result'],
                'message' => $outcome['message'],
                'summary' => $summary,
                'token' => $data['token'],
            ]);
    }
}
