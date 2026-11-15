<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\SeatHold;
use App\Services\PaymentService;
use App\Services\SettingsService;
use Illuminate\Console\Command;

class ExpireStaleBookings extends Command
{
    protected $signature = 'bookings:expire';

    protected $description = 'Expire unpaid bookings, expire their Checkout Sessions, and clear stale holds';

    public function handle(PaymentService $payments): int
    {
        $staleHolds = SeatHold::where('expires_at', '<=', now())->count();

        SeatHold::where('expires_at', '<=', now())->delete();

        // Pending bookings can outlive their holds (worker restarted, hold wiped),
        // so expire them on age as well instead of only on stale seat_holds.
        $pending = Booking::with('payment')
            ->where('status', 'pending_payment')
            ->where('created_at', '<=', now()->subMinutes((int) SettingsService::get('hold_minutes', 10)))
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

        $this->info("Cleared {$staleHolds} stale holds and expired {$expired} unpaid bookings.");

        return self::SUCCESS;
    }
}
