<?php

namespace App\Support;

final class BookingRules
{
    /** Sales stop this many minutes before the showtime starts. */
    public const SALES_CUTOFF_MINUTES = 15;

    /** Entry opens this many minutes before the showtime starts. */
    public const SCAN_OPEN_MINUTES = 60;

    /** Entry closes this many minutes after the showtime starts. */
    public const SCAN_CLOSE_MINUTES = 30;
}
