<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PricingService;
use App\Services\SettingsService;
use Stripe\Checkout\Session;
use Stripe\Refund;
use Stripe\Stripe;

class StripeGateway implements PaymentGateway
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createCheckoutSession(Booking $booking): array
    {
        $booking->loadMissing('showtime.movie', 'showtime.screen', 'seats');

        $showtime = $booking->showtime;
        $base = (int) $showtime->base_price_cents;
        $surcharge = PricingService::premiumSurchargeCents();
        $label = "{$showtime->movie->title} · {$showtime->screen->name} · ".$showtime->start_time->format('D j M, g:i A');

        $lineItems = [];
        $standard = $booking->seats->where('is_premium', false)->count();
        $premium = $booking->seats->where('is_premium', true)->count();

        if ($standard > 0) {
            $lineItems[] = $this->lineItem("{$label} — Standard seat", $base, $standard);
        }

        if ($premium > 0) {
            $lineItems[] = $this->lineItem("{$label} — Premium seat", $base + $surcharge, $premium);
        }

        if ($lineItems === []) {
            $lineItems[] = $this->lineItem("Movie tickets — {$showtime->movie->title}", $booking->total_amount_cents, 1);
        }

        $session = Session::create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel'),
            'client_reference_id' => (string) $booking->id,
            'metadata' => ['booking_id' => $booking->id],
            'payment_intent_data' => ['metadata' => ['booking_id' => $booking->id]],
        ]);

        return ['id' => $session->id, 'url' => $session->url];
    }

    public function expireCheckoutSession(string $sessionId): void
    {
        try {
            Session::expire($sessionId);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function refund(Payment $payment, ?int $amountCents = null): string
    {
        $amount = $amountCents ?? $payment->amount_cents;

        $refund = Refund::create([
            'payment_intent' => $payment->stripe_payment_intent,
            'amount' => $amount,
            'reason' => 'requested_by_customer',
        ], [
            'idempotency_key' => "booking-{$payment->booking_id}-refund-{$amount}",
        ]);

        return $refund->id;
    }

    private function lineItem(string $name, int $unitAmount, int $quantity): array
    {
        return [
            'price_data' => [
                'currency' => strtolower((string) SettingsService::get('currency', 'usd')),
                'product_data' => ['name' => $name],
                'unit_amount' => $unitAmount,
            ],
            'quantity' => $quantity,
        ];
    }
}
