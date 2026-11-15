<?php

namespace App\Contracts;

use App\Models\Booking;
use App\Models\Payment;

interface PaymentGateway
{
    /**
     * @return array{id: string, url: string}
     */
    public function createCheckoutSession(Booking $booking): array;

    public function expireCheckoutSession(string $sessionId): void;

    /** Returns the gateway refund id. */
    public function refund(Payment $payment, ?int $amountCents = null): string;
}
