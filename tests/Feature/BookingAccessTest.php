<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesCinema;
use Tests\TestCase;

class BookingAccessTest extends TestCase
{
    use CreatesCinema, RefreshDatabase;

    public function test_a_customer_cannot_view_another_customers_booking(): void
    {
        [, , $showtime, $seats] = $this->screening();
        $owner = $this->customer();
        $other = $this->customer();

        $booking = $this->booking($owner, $showtime, [$seats[0]]);

        $this->actingAs($other)->get(route('bookings.show', $booking))->assertForbidden();
    }

    public function test_a_customer_can_view_their_own_booking(): void
    {
        [, , $showtime, $seats] = $this->screening();
        $owner = $this->customer();

        $booking = $this->booking($owner, $showtime, [$seats[0]]);

        $this->actingAs($owner)->get(route('bookings.show', $booking))->assertOk();
    }

    public function test_a_customer_cannot_view_another_customers_ticket(): void
    {
        [, , $showtime, $seats] = $this->screening(['start_time' => now()->addHours(5)]);
        $owner = $this->customer();
        $other = $this->customer();

        $booking = $this->booking($owner, $showtime, [$seats[0]]);

        $ticket = \App\Models\Ticket::create([
            'booking_id' => $booking->id,
            'seat_id' => $seats[0]->id,
            'token' => \Illuminate\Support\Str::random(40),
            'status' => 'valid',
        ]);

        $this->actingAs($other)->get(route('tickets.show', $ticket->token))->assertForbidden();
    }
}
