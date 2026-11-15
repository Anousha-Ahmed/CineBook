<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Jobs\RefundBookingJob;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\SeatHold;
use Illuminate\Support\Facades\DB;

class RefundService
{
    public function __construct(private PaymentGateway $gateway)
    {
    }

    public function request(Booking $booking, string $reason): void
    {
        $payment = $booking->payment;

        if (! $payment || $payment->refund_status === 'succeeded') {
            return;
        }

        if ($payment->status !== 'successful') {
            return;
        }

        DB::transaction(function () use ($payment, $booking, $reason) {
            $payment->refresh();

            if ($payment->refund_status === 'succeeded') {
                return;
            }

            $payment->update([
                'refund_status' => 'pending',
                'refund_attempts' => $payment->refund_attempts + 1,
                'failure_reason' => null,
            ]);

            AuditLogger::log('refund.requested', $booking, [], $payment->only(['id', 'amount_cents']), ['reason' => $reason]);
        });

        RefundBookingJob::dispatch($booking->id, $reason);
    }

    /**
     * Cancel a booking once: void tickets, release seats and holds, then refund.
     * Concurrent duplicate requests leave the first result untouched.
     */
    public function cancelBooking(Booking $booking, string $reason, bool $markRefunded = false): void
    {
        $cancelled = DB::transaction(function () use ($booking, $reason, $markRefunded) {
            /** @var Booking|null $locked */
            $locked = Booking::lockForUpdate()->find($booking->id);

            if (! $locked || in_array($locked->status, ['cancelled', 'refunded', 'expired', 'failed'], true)) {
                return false;
            }

            $before = $locked->only(['status']);
            $seatIds = $locked->seats()->pluck('seats.id')->all();

            $locked->update([
                'status' => $markRefunded ? 'refunded' : 'cancelled',
            ]);

            $locked->tickets()->update(['status' => 'void']);
            $locked->seats()->detach();

            SeatHold::where('user_id', $locked->user_id)
                ->where('showtime_id', $locked->showtime_id)
                ->whereIn('seat_id', $seatIds)
                ->delete();

            AuditLogger::log('booking.cancelled', $locked, $before, ['status' => $locked->status], ['reason' => $reason]);

            return true;
        });

        if (! $cancelled) {
            return;
        }

        $fresh = $booking->fresh(['payment']);

        // An unpaid booking can still have a live Checkout Session; close it.
        if ($fresh->payment && $fresh->payment->status === 'pending') {
            if ($fresh->payment->stripe_session_id) {
                $this->gateway->expireCheckoutSession($fresh->payment->stripe_session_id);
            }

            $fresh->payment->update(['status' => 'failed', 'failure_reason' => $reason]);
        }

        $this->request($fresh, $reason);
    }

    public function refundablePaymentsQuery()
    {
        return Payment::query()
            ->where('status', 'successful')
            ->whereIn('refund_status', ['pending', 'failed']);
    }
}
