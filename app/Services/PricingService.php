<?php

namespace App\Services;

use App\Models\Seat;
use App\Models\Showtime;

class PricingService
{
    public static function premiumSurchargeCents(): int
    {
        return (int) SettingsService::get('premium_surcharge_cents', 200);
    }

    public static function seatPriceCents(Showtime $showtime, Seat $seat): int
    {
        return (int) $showtime->base_price_cents + ($seat->is_premium ? self::premiumSurchargeCents() : 0);
    }

    public static function totalCents(Showtime $showtime, iterable $seats): int
    {
        $total = 0;

        foreach ($seats as $seat) {
            $total += self::seatPriceCents($showtime, $seat);
        }

        return $total;
    }

    public static function format(int $cents): string
    {
        return '$'.number_format($cents / 100, 2);
    }
}
