<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Movie;
use App\Services\RefundService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $statuses = ['pending_payment', 'confirmed', 'cancelled', 'expired', 'refunded', 'failed'];

        $bookings = Booking::with(['user', 'showtime.movie', 'showtime.screen', 'payment'])
            ->withCount('tickets')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('movie'), fn ($q, $movieId) => $q->whereHas(
                'showtime',
                fn ($inner) => $inner->where('movie_id', $movieId)
            ))
            ->when($request->query('date'), fn ($q, $date) => $q->whereHas(
                'showtime',
                fn ($inner) => $inner->whereDate('start_time', $date)
            ))
            ->when($request->query('customer'), fn ($q, $customer) => $q->whereHas(
                'user',
                fn ($inner) => $inner->where('email', $customer)->orWhere('name', 'like', "%{$customer}%")
            ))
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('id', $search)
                        ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->boolean('refund_issues'), fn ($q) => $q->whereHas(
                'payment',
                fn ($inner) => $inner->whereIn('refund_status', ['pending', 'failed'])
            ))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $movies = Movie::orderBy('title')->get(['id', 'title']);

        return view('admin.bookings.index', compact('bookings', 'statuses', 'movies'));
    }

    public function show(Booking $booking)
    {
        $booking->load([
            'user',
            'showtime.movie',
            'showtime.screen',
            'seats',
            'tickets.seat',
            'payment',
        ]);

        $scans = $booking->tickets()->with('scans.staff')->get()
            ->flatMap(fn ($ticket) => $ticket->scans)
            ->sortByDesc('created_at');

        return view('admin.bookings.show', compact('booking', 'scans'));
    }

    public function cancel(Booking $booking, RefundService $refunds)
    {
        if (in_array($booking->status, ['cancelled', 'refunded'], true)) {
            return back()->with('error', 'This booking is already cancelled.');
        }

        // Admins may cancel and refund at any time, including after a scan (FR-CAN-4).
        $refunds->cancelBooking($booking, 'Cancelled by an administrator.');

        return back()->with('success', "Booking #{$booking->id} cancelled. Seats released and refund queued.");
    }
}
