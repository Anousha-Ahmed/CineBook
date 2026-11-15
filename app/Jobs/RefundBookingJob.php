<?php

namespace App\Jobs;

use App\Contracts\PaymentGateway;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\AuditLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Throwable;

class RefundBookingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    /** @var list<int> */
    public array $backoff = [60, 300, 900];

    public function __construct(public int $bookingId, public string $reason = 'Booking cancelled')
    {
    }

    public function handle(PaymentGateway $gateway): void
    {
        $payment = Payment::where('booking_id', $this->bookingId)->first();

        if (! $payment || $payment->refund_status === 'succeeded') {
            return;
        }

        if ($payment->status !== 'successful') {
            $payment->update(['refund_status' => 'none']);

            return;
        }

        try {
            $refundId = $gateway->refund($payment);

            DB::transaction(function () use ($payment, $refundId) {
                $payment->update([
                    'refund_status' => 'succeeded',
                    'stripe_refund_id' => $refundId,
                    'refunded_amount_cents' => $payment->amount_cents,
                    'status' => 'refunded',
                    'failure_reason' => null,
                ]);

                AuditLogger::log('refund.issued', Booking::find($this->bookingId), [], [
                    'refund_id' => $refundId,
                    'amount_cents' => $payment->amount_cents,
                ], ['reason' => $this->reason]);
            });
        } catch (Throwable $e) {
            $payment->update([
                'refund_status' => 'failed',
                'failure_reason' => $e->getMessage(),
                'refund_attempts' => $payment->refund_attempts + 1,
            ]);

            AuditLogger::log('refund.failed', Booking::find($this->bookingId), [], [], [
                'reason' => $this->reason,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Payment::where('booking_id', $this->bookingId)->update([
            'refund_status' => 'failed',
            'failure_reason' => $exception->getMessage(),
        ]);
    }
}
