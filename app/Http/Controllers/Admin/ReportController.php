<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);

        $bookings = Booking::with(['user', 'showtime.movie', 'showtime.screen', 'payment'])
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        $summary = [
            'bookings' => (clone $bookings)->count(),
            'confirmed' => (clone $bookings)->where('status', 'confirmed')->count(),
            'cancelled' => (clone $bookings)->whereIn('status', ['cancelled', 'refunded'])->count(),
            'gross_cents' => Payment::where('status', 'successful')
                ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
                ->sum('amount_cents'),
            'refunded_cents' => Payment::where('refund_status', 'succeeded')
                ->whereBetween('updated_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
                ->sum('refunded_amount_cents'),
        ];

        $summary['net_cents'] = $summary['gross_cents'] - $summary['refunded_cents'];

        $daily = Booking::selectRaw('date(created_at) as day, count(*) as bookings, sum(total_amount_cents) as total')
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->whereIn('status', ['confirmed'])
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $perMovie = Booking::query()
            ->join('showtimes', 'showtimes.id', '=', 'bookings.showtime_id')
            ->join('movies', 'movies.id', '=', 'showtimes.movie_id')
            ->whereBetween('bookings.created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->where('bookings.status', 'confirmed')
            ->selectRaw('movies.title, count(distinct bookings.id) as bookings, sum(bookings.total_amount_cents) as total')
            ->groupBy('movies.id', 'movies.title')
            ->orderByDesc('total')
            ->get();

        return view('admin.reports.index', compact('from', 'to', 'summary', 'daily', 'perMovie'));
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);

        $filename = 'cinebook-sales-'.$from->toDateString().'-to-'.$to->toDateString().'.csv';

        return response()->streamDownload(function () use ($from, $to) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Booking', 'Created', 'Customer', 'Email', 'Movie', 'Showtime', 'Screen', 'Seats', 'Status', 'Total', 'Payment', 'Refund']);

            Booking::with(['user', 'showtime.movie', 'showtime.screen', 'seats', 'payment'])
                ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
                ->orderBy('id')
                ->chunk(200, function ($bookings) use ($handle) {
                    foreach ($bookings as $booking) {
                        fputcsv($handle, [
                            $booking->id,
                            $booking->created_at->toDateTimeString(),
                            $booking->user?->name,
                            $booking->user?->email,
                            $booking->showtime?->movie?->title,
                            $booking->showtime?->start_time?->toDateTimeString(),
                            $booking->showtime?->screen?->name,
                            $booking->seats->map->label->implode(' '),
                            $booking->status,
                            number_format($booking->total_amount_cents / 100, 2, '.', ''),
                            $booking->payment?->status,
                            $booking->payment?->refund_status,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function range(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->subDays(29)->startOfDay();

        $to = $request->filled('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }
}
