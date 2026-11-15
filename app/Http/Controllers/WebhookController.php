<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function handle(Request $request, PaymentService $payments)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook');

        if (! $signature || ! $secret) {
            return response()->json(['error' => 'Webhook is not configured'], 400);
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $session = $event->data->object;
        $sessionId = $session->id ?? null;
        $bookingId = $session->client_reference_id ?? null;

        switch ($event->type) {
            case 'checkout.session.completed':
                // Card payments arrive as paid; delayed methods only complete when paid.
                if (($session->payment_status ?? null) === 'paid' && $bookingId) {
                    $payments->confirmFromWebhook(
                        $event->id,
                        $event->type,
                        $sessionId,
                        $session->payment_intent ?? null,
                        $bookingId,
                    );
                }
                break;

            case 'checkout.session.expired':
                $booking = $this->bookingForSession($sessionId);
                if ($booking) {
                    $payments->expirePendingBooking($booking, false);
                }
                break;

            case 'checkout.session.async_payment_failed':
                $payments->failPayment(
                    $sessionId ?? '',
                    $bookingId ? (int) $bookingId : null,
                    'The payment method was declined.',
                );
                break;

            case 'payment_intent.payment_failed':
                $payments->failPayment(
                    $session->metadata['checkout_session_id'] ?? '',
                    null,
                    'The payment could not be completed.',
                );
                break;
        }

        Log::info('Stripe webhook processed', ['event' => $event->type]);

        return response()->json(['status' => 'ok'], 200);
    }

    private function bookingForSession(?string $sessionId)
    {
        if (! $sessionId) {
            return null;
        }

        return Payment::where('stripe_session_id', $sessionId)->first()?->booking;
    }
}
