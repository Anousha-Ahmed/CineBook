<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();

        $revenueCents = Payment::where('status', 'successful')
            ->whereDate('created_at', $today)
            ->sum('amount_cents');

        $bookingsToday = Booking::whereDate('created_at', $today)->count();

        $confirmedToday = Booking::where('status', 'confirmed')
            ->whereDate('created_at', $today)
            ->count();

        $ticketsSold = Ticket::whereHas('booking', fn ($q) => $q->where('status', 'confirmed'))->count();

        $todayShowtimeIds = Showtime::whereDate('start_time', $today)->pluck('id');

        $soldToday = (int) DB::table('booking_seat')
            ->whereIn('booking_id', Booking::whereIn('showtime_id', $todayShowtimeIds)
                ->whereIn('status', ['confirmed', 'pending_payment'])
                ->select('id'))
            ->count();

        $capacityToday = (int) Showtime::whereDate('start_time', $today)
            ->get()
            ->sum(fn ($s) => Seat::where('screen_id', $s->screen_id)->count());

        $occupancy = $capacityToday > 0 ? round(($soldToday / $capacityToday) * 100) : 0;

        $recentBookings = Booking::with(['user', 'showtime.movie', 'showtime.screen'])
            ->latest()
            ->take(8)
            ->get();

        $todayShowtimes = Showtime::with(['movie', 'screen'])
            ->whereDate('start_time', $today)
            ->where('start_time', '>', now()->subHours(2))
            ->orderBy('start_time')
            ->take(6)
            ->get();

        $soldByShowtime = DB::table('booking_seat')
            ->join('bookings', 'bookings.id', '=', 'booking_seat.booking_id')
            ->whereIn('bookings.showtime_id', $todayShowtimes->pluck('id'))
            ->whereIn('bookings.status', ['confirmed', 'pending_payment'])
            ->selectRaw('bookings.showtime_id as showtime_id, count(*) as total')
            ->groupBy('bookings.showtime_id')
            ->pluck('total', 'showtime_id');

        $refundIssues = Payment::whereIn('refund_status', ['pending', 'failed'])->count();
        $failedRefunds = Payment::where('refund_status', 'failed')->count();
        $failedJobs = DB::table('failed_jobs')->count();

        return view('admin.dashboard', compact(
            'revenueCents',
            'bookingsToday',
            'confirmedToday',
            'ticketsSold',
            'occupancy',
            'recentBookings',
            'todayShowtimes',
            'soldByShowtime',
            'refundIssues',
            'failedRefunds',
            'failedJobs',
        ));
    }
}
