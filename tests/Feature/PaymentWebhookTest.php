<?php

namespace Tests\Feature;

use App\Models\StripeEvent;
use App\Models\Ticket;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesCinema;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use CreatesCinema, RefreshDatabase;

    public function test_a_webhook_confirms_a_booking_exactly_once_even_if_delivered_twice(): void
    {
        [, , $showtime, $seats] = $this->screening();
        $user = $this->customer();
        $booking = $this->booking($user, $showtime, [$seats[0], $seats[1]], 'pending_payment');
        $sessionId = $booking->payment->stripe_session_id;

        $payments = app(PaymentService::class);

        $payments->confirmFromWebhook('evt_duplicate', 'checkout.session.completed', $sessionId, 'pi_paid', $booking->id);
        $payments->confirmFromWebhook('evt_duplicate', 'checkout.session.completed', $sessionId, 'pi_paid', $booking->id);

        $booking->refresh();

        $this->assertSame('confirmed', $booking->status);
        $this->assertSame(2, Ticket::where('booking_id', $booking->id)->count());
        $this->assertSame(1, StripeEvent::where('event_id', 'evt_duplicate')->count());
        $this->assertSame('successful', $booking->payment->fresh()->status);
    }

    public function test_a_webhook_with_an_invalid_signature_is_rejected(): void
    {
        config(['services.stripe.webhook' => 'whsec_test_secret']);

        $response = $this->call('POST', '/stripe/webhook', [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => 't=1,v1=definitely-not-valid',
        ], json_encode(['type' => 'checkout.session.completed']));

        $response->assertStatus(400);
    }

    public function test_the_success_redirect_never_confirms_a_booking(): void
    {
        [, , $showtime, $seats] = $this->screening();
        $user = $this->customer();
        $booking = $this->booking($user, $showtime, [$seats[0]], 'pending_payment');

        $response = $this->actingAs($user)->get(route('checkout.success', [
            'session_id' => $booking->payment->stripe_session_id,
        ]));

        $response->assertOk();
        $response->assertSee('Payment processing');
        $this->assertSame('pending_payment', $booking->fresh()->status);
        $this->assertSame(0, Ticket::where('booking_id', $booking->id)->count());
    }
}
