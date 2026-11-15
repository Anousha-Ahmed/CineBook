<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\SeatHold;
use App\Services\PaymentService;
use Illuminate\Console\Command;

class ExpireStaleBookings extends Command
{
    protected $signature = 'bookings:expire';

    protected $description = 'Expire unpaid bookings, expire their Checkout Sessions, and clear stale holds';

    public function handle(PaymentService $payments): int
    {
        $held = SeatHold::where('expires_at', '<=', now())->get();
        $staleShowtimes = $held->pluck('showtime_id')->unique();

        SeatHold::where('expires_at', '<=', now())->delete();

        $pending = Booking::with('payment')
            ->where('status', 'pending_payment')
            ->whereIn('showtime_id', $staleShowtimes)
            ->get();

        $expired = 0;

        foreach ($pending as $booking) {
            $stillHeld = SeatHold::where('user_id', $booking->user_id)
                ->where('showtime_id', $booking->showtime_id)
                ->where('expires_at', '>', now())
                ->exists();

            if ($stillHeld) {
                continue;
            }

            $payments->expirePendingBooking($booking);
            $expired++;
        }

        $this->info("Cleared {$held->count()} stale holds and expired {$expired} unpaid bookings.");

        return self::SUCCESS;
    }
}
