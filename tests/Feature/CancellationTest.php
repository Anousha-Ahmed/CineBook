<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesCinema;
use Tests\TestCase;

class CancellationTest extends TestCase
{
    use CreatesCinema, RefreshDatabase;

    public function test_cancellation_inside_the_two_hour_window_is_rejected(): void
    {
        [, , $showtime, $seats] = $this->screening(['start_time' => now()->addHour()]);
        $user = $this->customer();
        $booking = $this->booking($user, $showtime, [$seats[0]]);

        $response = $this->actingAs($user)->post(route('bookings.cancel', $booking));

        $response->assertSessionHas('error');
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertSame('successful', $booking->payment->fresh()->status);
    }

    public function test_cancelling_outside_the_window_refunds_and_voids_the_booking(): void
    {
        [, , $showtime, $seats] = $this->screening(['start_time' => now()->addHours(6)]);
        $user = $this->customer();
        $booking = $this->booking($user, $showtime, [$seats[0], $seats[1]]);

        Ticket::create([
            'booking_id' => $booking->id,
            'seat_id' => $seats[0]->id,
            'token' => Str::random(40),
            'status' => 'valid',
        ]);

        app(RefundService::class)->cancelBooking($booking, 'Customer cancelled the booking.');

        $booking->refresh();
        $payment = $booking->payment->fresh();

        $this->assertSame('cancelled', $booking->status);
        $this->assertSame('void', $booking->tickets()->first()?->status);
        $this->assertSame('refunded', $payment->status);
        $this->assertSame('succeeded', $payment->refund_status);
        $this->assertSame(0, $booking->seats()->count());
    }

    public function test_a_booking_with_a_scanned_ticket_cannot_be_cancelled(): void
    {
        [, , $showtime, $seats] = $this->screening(['start_time' => now()->addHours(6)]);
        $user = $this->customer();
        $booking = $this->booking($user, $showtime, [$seats[0]]);

        Ticket::create([
            'booking_id' => $booking->id,
            'seat_id' => $seats[0]->id,
            'token' => Str::random(40),
            'status' => 'used',
            'used_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('bookings.cancel', $booking));

        $response->assertSessionHas('error');
        $this->assertSame('confirmed', $booking->fresh()->status);
    }
}
