<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Mail\BookingCancelledMail;
use App\Mail\BookingConfirmedMail;
use App\Mail\PaymentFailedMail;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Seat;
use App\Models\SeatHold;
use App\Models\Showtime;
use App\Models\StripeEvent;
use App\Models\Ticket;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(private PaymentGateway $gateway)
    {
    }

    /**
     * Create (or reuse) the pending booking for the current holds and start Stripe Checkout.
     *
     * @param  Collection<int, SeatHold>  $holds
     */
    public function startCheckout(Collection $holds, Showtime $showtime, int $userId): string
    {
        $seatIds = $holds->pluck('seat_id')->map(fn ($id) => (int) $id)->all();
        $totalCents = PricingService::totalCents($showtime, $holds->pluck('seat'));
        $holdExpiresAt = $holds->min('expires_at');

        [$booking, $payment] = DB::transaction(function () use ($userId, $showtime, $seatIds, $totalCents, $holdExpiresAt) {
            $booking = Booking::where('user_id', $userId)
                ->where('showtime_id', $showtime->id)
                ->where('status', 'pending_payment')
                ->where('created_at', '>=', $holdExpiresAt->copy()->subMinutes((int) SettingsService::get('hold_minutes', 10)))
                ->latest()
                ->lockForUpdate()
                ->first();

            if ($booking) {
                $booking->update(['total_amount_cents' => $totalCents]);
                $booking->seats()->sync($seatIds);
            } else {
                $booking = Booking::create([
                    'user_id' => $userId,
                    'showtime_id' => $showtime->id,
                    'status' => 'pending_payment',
                    'total_amount_cents' => $totalCents,
                ]);

                $booking->seats()->sync($seatIds);
            }

            $payment = Payment::firstOrCreate(
                ['booking_id' => $booking->id],
                ['amount_cents' => $totalCents, 'status' => 'pending'],
            );

            $payment->update(['amount_cents' => $totalCents, 'status' => 'pending']);

            return [$booking, $payment];
        });

        if ($payment->stripe_session_id) {
            $this->gateway->expireCheckoutSession($payment->stripe_session_id);
        }

        $session = $this->gateway->createCheckoutSession($booking->fresh(['showtime.movie', 'showtime.screen', 'seats']));

        $payment->update(['stripe_session_id' => $session['id']]);

        return $session['url'];
    }

    /**
     * Called only from the signature-verified Stripe webhook.
     */
    public function confirmFromWebhook(
        string $eventId,
        string $eventType,
        string $sessionId,
        ?string $paymentIntentId,
        int|string $bookingId,
    ): ?Booking {
        if (StripeEvent::where('event_id', $eventId)->exists()) {
            return Booking::find($bookingId);
        }

        $result = null;

        try {
            $result = DB::transaction(function () use ($eventId, $eventType, $sessionId, $paymentIntentId, $bookingId) {
                StripeEvent::create(['event_id' => $eventId, 'type' => $eventType]);

                /** @var Booking|null $booking */
                $booking = Booking::with('seats', 'showtime', 'payment')->lockForUpdate()->find($bookingId);

                if (! $booking) {
                    return null;
                }

                if ($booking->status === 'confirmed') {
                    return ['booking' => $booking, 'confirmed' => false];
                }

                $payment = $booking->payment;
                $payment?->update([
                    'status' => 'successful',
                    'stripe_session_id' => $sessionId,
                    'stripe_payment_intent' => $paymentIntentId,
                ]);

                $seatIds = $booking->seats->pluck('id')->all();

                // The money arrived, but the seats were taken by someone else while the hold expired.
                if ($booking->status !== 'pending_payment' || $this->seatsLost($booking, $seatIds)) {
                    $booking->update(['status' => 'refunded']);
                    $payment?->update([
                        'refund_status' => 'pending',
                        'refund_attempts' => DB::raw('refund_attempts + 1'),
                    ]);

                    DB::afterCommit(function () use ($booking) {
                        Mail::to($booking->user)->queue(new BookingCancelledMail(
                            $booking,
                            'Your payment arrived after the seat hold expired and another customer took the seats. A full refund is being processed.'
                        ));

                        \App\Jobs\RefundBookingJob::dispatch($booking->id, 'Paid after hold expiry');
                    });

                    return ['booking' => $booking, 'confirmed' => false];
                }

                $booking->update(['status' => 'confirmed']);

                SeatHold::where('showtime_id', $booking->showtime_id)
                    ->whereIn('seat_id', $seatIds)
                    ->delete();

                $existing = Ticket::where('booking_id', $booking->id)->pluck('seat_id')->all();

                foreach (array_diff($seatIds, $existing) as $seatId) {
                    Ticket::create([
                        'booking_id' => $booking->id,
                        'seat_id' => $seatId,
                        'token' => Str::random(40),
                        'status' => 'valid',
                    ]);
                }

                DB::afterCommit(function () use ($booking) {
                    Mail::to($booking->user)->queue(new BookingConfirmedMail($booking->fresh()));
                });

                return ['booking' => $booking, 'confirmed' => true];
            });
        } catch (QueryException $e) {
            // Concurrent duplicate of the same event: unique index on stripe_events.event_id.
            if (StripeEvent::where('event_id', $eventId)->exists()) {
                return Booking::find($bookingId);
            }

            throw $e;
        }

        return $result['booking'] ?? null;
    }

    /**
     * @param  array<int, int>  $seatIds
     */
    private function seatsLost(Booking $booking, array $seatIds): bool
    {
        $takenByAnotherBooking = DB::table('booking_seat')
            ->join('bookings', 'bookings.id', '=', 'booking_seat.booking_id')
            ->where('bookings.showtime_id', $booking->showtime_id)
            ->where('bookings.id', '!=', $booking->id)
            ->whereIn('bookings.status', ['confirmed'])
            ->whereIn('booking_seat.seat_id', $seatIds)
            ->exists();

        $heldBySomeoneElse = SeatHold::where('showtime_id', $booking->showtime_id)
            ->whereIn('seat_id', $seatIds)
            ->where('user_id', '!=', $booking->user_id)
            ->where('expires_at', '>', now())
            ->exists();

        return $takenByAnotherBooking || $heldBySomeoneElse;
    }

    public function expirePendingBooking(Booking $booking, bool $expireSession = true): void
    {
        $booking = Booking::with('payment')->find($booking->id);

        if (! $booking || $booking->status !== 'pending_payment') {
            return;
        }

        if ($expireSession && $booking->payment?->stripe_session_id) {
            $this->gateway->expireCheckoutSession($booking->payment->stripe_session_id);
        }

        DB::transaction(function () use ($booking) {
            /** @var Booking|null $booking */
            $booking = Booking::with('payment')->lockForUpdate()->find($booking->id);

            if (! $booking || $booking->status !== 'pending_payment') {
                return;
            }

            $seatIds = $booking->seats()->pluck('seats.id')->all();

            $booking->update(['status' => 'expired']);
            $booking->payment?->update([
                'status' => 'failed',
                'failure_reason' => 'Checkout session expired before payment.',
            ]);
            $booking->seats()->detach();
            SeatHold::where('user_id', $booking->user_id)
                ->where('showtime_id', $booking->showtime_id)
                ->whereIn('seat_id', $seatIds)
                ->delete();

            DB::afterCommit(function () use ($booking) {
                Mail::to($booking->user)->queue(new PaymentFailedMail(
                    $booking,
                    'Your seat hold expired before payment was completed.'
                ));
            });
        });
    }

    public function failPayment(string $sessionId, ?int $bookingId, string $reason): ?Booking
    {
        $booking = $bookingId
            ? Booking::with('payment')->find($bookingId)
            : Booking::with('payment')->whereHas(
                'payment',
                fn ($q) => $q->where('stripe_session_id', $sessionId)
            )->first();

        if (! $booking || $booking->status !== 'pending_payment') {
            return $booking;
        }

        DB::transaction(function () use ($booking, $reason) {
            /** @var Booking|null $locked */
            $locked = Booking::with('payment')->lockForUpdate()->find($booking->id);

            if (! $locked || $locked->status !== 'pending_payment') {
                return;
            }

            $seatIds = $locked->seats()->pluck('seats.id')->all();

            $locked->update(['status' => 'failed']);
            $locked->payment?->update(['status' => 'failed', 'failure_reason' => $reason]);
            $locked->seats()->detach();
            $locked->tickets()->update(['status' => 'void']);
            SeatHold::where('user_id', $locked->user_id)
                ->where('showtime_id', $locked->showtime_id)
                ->whereIn('seat_id', $seatIds)
                ->delete();

            DB::afterCommit(function () use ($locked, $reason) {
                Mail::to($locked->user)->queue(new PaymentFailedMail($locked, $reason));
            });
        });

        return $booking;
    }
}
