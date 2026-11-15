<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Str;

class FakeGateway implements PaymentGateway
{
    /** @var list<int> */
    public array $expiredSessions = [];

    /** @var list<array{payment: int, amount: int}> */
    public array $refunds = [];

    public function createCheckoutSession(Booking $booking): array
    {
        $id = 'cs_fake_'.Str::lower(Str::random(16));

        return ['id' => $id, 'url' => route('checkout.success').'?session_id='.$id];
    }

    public function expireCheckoutSession(string $sessionId): void
    {
        $this->expiredSessions[] = $sessionId;
    }

    public function refund(Payment $payment, ?int $amountCents = null): string
    {
        $this->refunds[] = ['payment' => $payment->id, 'amount' => $amountCents ?? $payment->amount_cents];

        return 're_fake_'.Str::lower(Str::random(16));
    }
}
