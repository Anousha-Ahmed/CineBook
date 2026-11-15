<?php

namespace Tests\Feature;

use App\Models\SeatHold;
use App\Services\SeatHoldService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesCinema;
use Tests\TestCase;

class SeatHoldTest extends TestCase
{
    use CreatesCinema, RefreshDatabase;

    public function test_a_seat_cannot_be_held_twice_for_the_same_showtime(): void
    {
        [, , $showtime, $seats] = $this->screening();

        $service = app(SeatHoldService::class);
        $service->holdSeats($this->customer()->id, $showtime->id, [$seats[0]->id]);

        try {
            $service->holdSeats($this->customer()->id, $showtime->id, [$seats[0]->id]);
            $this->fail('A second customer was able to hold the same seat.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('no longer available', $e->getMessage());
        }

        $this->assertSame(
            1,
            SeatHold::where('showtime_id', $showtime->id)->where('seat_id', $seats[0]->id)->count()
        );
    }

    public function test_an_expired_hold_releases_the_seat(): void
    {
        [, , $showtime, $seats] = $this->screening();

        $first = $this->customer();

        SeatHold::create([
            'user_id' => $first->id,
            'showtime_id' => $showtime->id,
            'seat_id' => $seats[0]->id,
            'expires_at' => now()->subMinute(),
        ]);

        $second = $this->customer();
        $expiresAt = app(SeatHoldService::class)->holdSeats($second->id, $showtime->id, [$seats[0]->id]);

        $this->assertTrue($expiresAt->isFuture());
        $this->assertDatabaseHas('seat_holds', [
            'showtime_id' => $showtime->id,
            'seat_id' => $seats[0]->id,
            'user_id' => $second->id,
        ]);
        $this->assertSame(1, SeatHold::where('showtime_id', $showtime->id)->count());
    }

    public function test_sales_close_fifteen_minutes_before_the_showtime(): void
    {
        [, , $showtime, $seats] = $this->screening(['start_time' => now()->addMinutes(10)]);

        $this->expectExceptionMessage('closed');

        app(SeatHoldService::class)->holdSeats($this->customer()->id, $showtime->id, [$seats[0]->id]);
    }
}
