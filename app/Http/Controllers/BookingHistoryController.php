<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\RefundService;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class BookingHistoryController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab') === 'past' ? 'past' : 'upcoming';

        $query = auth()->user()->bookings()
            ->with(['showtime.movie', 'showtime.screen', 'tickets.seat', 'seats', 'payment'])
            ->latest();

        if ($tab === 'upcoming') {
            $query->whereIn('status', ['confirmed', 'pending_payment'])
                ->whereHas('showtime', fn ($q) => $q->where('start_time', '>', now()));
        } else {
            $query->where(function ($q) {
                $q->whereIn('status', ['cancelled', 'expired', 'failed', 'refunded'])
                    ->orWhereHas('showtime', fn ($inner) => $inner->where('start_time', '<=', now()));
            });
        }

        $bookings = $query->paginate(8)->withQueryString();

        return view('bookings.index', compact('bookings', 'tab'));
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['showtime.movie', 'showtime.screen', 'seats', 'tickets.seat', 'payment']);

        return view('bookings.show', compact('booking'));
    }

    public function cancel(Booking $booking, RefundService $refunds)
    {
        $this->authorize('cancel', $booking);

        if ($booking->hasUsedTickets()) {
            return back()->with('error', 'Tickets from this booking have already been scanned and cannot be cancelled.');
        }

        if (! $booking->isCancellable()) {
            $cutoff = (int) SettingsService::get('cancel_cutoff_hours', 2);

            return back()->with('error', "This booking can no longer be cancelled (cutoff: {$cutoff} hours before showtime).");
        }

        $refunds->cancelBooking($booking, 'Customer cancelled the booking.');

        return redirect()->route('bookings.index', ['tab' => 'past'])
            ->with('success', 'Booking cancelled. Your seats are released and the refund is being processed.');
    }
}
