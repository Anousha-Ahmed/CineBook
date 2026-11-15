<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\SeatHold;
use App\Services\PaymentService;
use App\Services\PricingService;
use App\Services\SeatHoldService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $holds = $this->activeHolds($request->integer('showtime') ?: null);

        if ($holds->isEmpty()) {
            return redirect()->route('movies.index')->with('error', 'Your seat hold expired. Please pick your seats again.');
        }

        $showtime = $holds->first()->showtime;

        if (! $showtime->isOnSale()) {
            return redirect()->route('movies.index')->with('error', 'Ticket sales for this show have closed.');
        }

        $totalCents = PricingService::totalCents($showtime, $holds->pluck('seat'));
        $expiresAt = $holds->min('expires_at');
        $secondsRemaining = max(0, (int) now()->diffInSeconds($expiresAt, false));

        return view('checkout.index', compact('holds', 'showtime', 'totalCents', 'expiresAt', 'secondsRemaining'));
    }

    public function process(Request $request, PaymentService $payments)
    {
        $holds = $this->activeHolds($request->integer('showtime') ?: null);

        if ($holds->isEmpty()) {
            return redirect()->route('movies.index')->with('error', 'Your seat hold expired. Please select seats again.');
        }

        $showtime = $holds->first()->showtime;

        if (! $showtime->isOnSale()) {
            return redirect()->route('movies.index')->with('error', 'Ticket sales for this show have closed.');
        }

        try {
            $checkoutUrl = $payments->startCheckout($holds, $showtime, auth()->id());

            return redirect()->away($checkoutUrl);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'We could not start the payment. Please try again.');
        }
    }

    /**
     * The success redirect never confirms a booking. It only reports whether the
     * signature-verified webhook has already confirmed the payment.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        $booking = $sessionId
            ? Payment::with(['booking.tickets.seat', 'booking.showtime.movie', 'booking.showtime.screen', 'booking.seats'])
                ->where('stripe_session_id', $sessionId)
                ->whereHas('booking', fn ($query) => $query->where('user_id', auth()->id()))
                ->first()?->booking
            : Booking::with(['tickets.seat', 'showtime.movie', 'showtime.screen', 'seats'])
                ->where('user_id', auth()->id())
                ->latest()
                ->first();

        $paid = $booking?->status === 'confirmed';
        $processing = $booking?->status === 'pending_payment';

        return view('checkout.success', compact('booking', 'paid', 'processing'));
    }

    public function cancel(SeatHoldService $holds, PaymentService $payments)
    {
        $booking = Booking::with('payment')
            ->where('user_id', auth()->id())
            ->where('status', 'pending_payment')
            ->latest()
            ->first();

        if ($booking) {
            $payments->failPayment(
                $booking->payment?->stripe_session_id ?? 'customer-cancelled',
                $booking->id,
                'The customer cancelled the payment.',
            );

            $holds->releaseForUser(auth()->id(), $booking->showtime_id);
        }

        return redirect()->route('movies.index')->with('error', 'Payment was cancelled. Your seats have been released.');
    }

    private function activeHolds(?int $showtimeId = null)
    {
        $query = SeatHold::where('user_id', auth()->id())
            ->where('expires_at', '>', now());

        // A booking always belongs to exactly one showtime, so never mix holds
        // from different shows into the same checkout.
        $showtimeId ??= (int) SeatHold::where('user_id', auth()->id())
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->value('showtime_id');

        if (! $showtimeId) {
            return SeatHold::newModelQuery()->whereRaw('1 = 0')->get();
        }

        return $query->where('showtime_id', $showtimeId)
            ->with(['seat', 'showtime.movie', 'showtime.screen'])
            ->get();
    }
}
