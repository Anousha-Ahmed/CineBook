<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Services\TicketScanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesCinema;
use Tests\TestCase;

class TicketScanTest extends TestCase
{
    use CreatesCinema, RefreshDatabase;

    public function test_a_duplicate_qr_scan_is_rejected(): void
    {
        [, , $showtime, $seats] = $this->screening(['start_time' => now()->addMinutes(10)]);
        $user = $this->customer();
        $booking = $this->booking($user, $showtime, [$seats[0]]);

        $ticket = Ticket::create([
            'booking_id' => $booking->id,
            'seat_id' => $seats[0]->id,
            'token' => Str::random(40),
            'status' => 'valid',
        ]);

        $scanner = app(TicketScanService::class);

        $first = $scanner->scan($ticket->token, null, $showtime->id);
        $second = $scanner->scan($ticket->token, null, $showtime->id);

        $this->assertSame('valid', $first['result']);
        $this->assertSame('already_used', $second['result']);
        $this->assertSame('used', $ticket->fresh()->status);
        $this->assertNotNull($ticket->fresh()->used_at);
    }

    public function test_a_ticket_for_another_showtime_is_rejected(): void
    {
        [, , $showtime, $seats] = $this->screening(['start_time' => now()->addMinutes(10)]);
        [, , $otherShowtime] = $this->screening(['start_time' => now()->addMinutes(20)]);
        $user = $this->customer();
        $booking = $this->booking($user, $showtime, [$seats[0]]);

        $ticket = Ticket::create([
            'booking_id' => $booking->id,
            'seat_id' => $seats[0]->id,
            'token' => Str::random(40),
            'status' => 'valid',
        ]);

        $result = app(TicketScanService::class)->scan($ticket->token, null, $otherShowtime->id);

        $this->assertSame('wrong_showtime', $result['result']);
        $this->assertSame('valid', $ticket->fresh()->status);
    }

    public function test_a_ticket_scanned_before_the_window_opens_is_rejected(): void
    {
        [, , $showtime, $seats] = $this->screening(['start_time' => now()->addHours(3)]);
        $user = $this->customer();
        $booking = $this->booking($user, $showtime, [$seats[0]]);

        $ticket = Ticket::create([
            'booking_id' => $booking->id,
            'seat_id' => $seats[0]->id,
            'token' => Str::random(40),
            'status' => 'valid',
        ]);

        $result = app(TicketScanService::class)->scan($ticket->token, null, $showtime->id);

        $this->assertSame('too_early', $result['result']);
        $this->assertSame('valid', $ticket->fresh()->status);
    }
}
