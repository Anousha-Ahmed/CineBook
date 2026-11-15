<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesCinema;
use Tests\TestCase;

class StaffAccessTest extends TestCase
{
    use CreatesCinema, RefreshDatabase;

    public function test_staff_cannot_hold_seats(): void
    {
        [, , $showtime, $seats] = $this->screening();
        $staff = $this->customer('staff');

        $this->actingAs($staff)
            ->post(route('booking.hold', $showtime), ['seat_ids' => [$seats[0]->id]])
            ->assertForbidden();

        $this->assertDatabaseCount('seat_holds', 0);
    }

    public function test_staff_cannot_open_checkout_or_booking_history(): void
    {
        $staff = $this->customer('staff');

        $this->actingAs($staff)->get(route('checkout'))->assertForbidden();
        $this->actingAs($staff)->get(route('bookings.index'))->assertForbidden();
    }

    public function test_staff_can_open_the_scanner_with_todays_bookings(): void
    {
        [, , $showtime, $seats] = $this->screening(['start_time' => now()->startOfDay()->addHours(20)]);
        $staff = $this->customer('staff');
        $customer = $this->customer();

        $booking = $this->booking($customer, $showtime, [$seats[0]]);

        $this->actingAs($staff)
            ->get(route('staff.scan'))
            ->assertOk()
            ->assertSee('#'.$booking->id);
    }

    public function test_staff_can_look_up_a_ticket_by_its_token(): void
    {
        [, , $showtime, $seats] = $this->screening();
        $staff = $this->customer('staff');
        $customer = $this->customer();

        $booking = $this->booking($customer, $showtime, [$seats[0]]);

        $ticket = \App\Models\Ticket::create([
            'booking_id' => $booking->id,
            'seat_id' => $seats[0]->id,
            'token' => \Illuminate\Support\Str::random(40),
            'status' => 'valid',
        ]);

        $this->actingAs($staff)->get(route('tickets.show', $ticket->token))->assertOk();
    }
}
