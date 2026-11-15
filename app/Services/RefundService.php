<?php

namespace App\Services;

use App\Jobs\RefundBookingJob;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class RefundService
{
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
     * Cancel a paid booking: void tickets, release seats, refund the payment.
     */
    public function cancelBooking(Booking $booking, string $reason, bool $markRefunded = false): void
    {
        DB::transaction(function () use ($booking, $reason, $markRefunded) {
            $before = $booking->only(['status']);

            $booking->update([
                'status' => $markRefunded ? 'refunded' : 'cancelled',
            ]);

            $booking->tickets()->update(['status' => 'void']);
            $booking->seats()->detach();

            AuditLogger::log('booking.cancelled', $booking, $before, ['status' => $booking->status], ['reason' => $reason]);
        });

        $this->request($booking->fresh(), $reason);
    }

    public function refundablePaymentsQuery()
    {
        return Payment::query()
            ->where('status', 'successful')
            ->whereIn('refund_status', ['pending', 'failed']);
    }
}
