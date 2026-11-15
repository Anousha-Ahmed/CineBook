<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->range($request);

        $summary = $this->summary($from, $to);
        $daily = $this->daily($from, $to);
        $perMovie = $this->perMovie($from, $to);

        return view('admin.reports.index', compact('from', 'to', 'summary', 'daily', 'perMovie'));
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);

        $summary = $this->summary($from, $to);
        $daily = $this->daily($from, $to);
        $perMovie = $this->perMovie($from, $to);
        $filename = 'cinebook-sales-'.$from->toDateString().'-to-'.$to->toDateString().'.csv';

        return response()->streamDownload(function () use ($summary, $daily, $perMovie) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['CineBook sales report']);
            fputcsv($handle, ['Bookings', $summary['bookings'], 'Confirmed', $summary['confirmed'], 'Cancelled/refunded', $summary['cancelled']]);
            fputcsv($handle, ['Entire range']);
            fputcsv($handle, ['Tickets', 'Gross', 'Refunds', 'Net']);
            fputcsv($handle, [
                $summary['tickets'],
                $this->money($summary['gross_cents']),
                $this->money($summary['refunded_cents']),
                $this->money($summary['net_cents']),
            ]);

            fputcsv($handle, []);
            fputcsv($handle, ['Sales by day']);
            fputcsv($handle, ['Date', 'Tickets', 'Bookings', 'Gross', 'Refunds', 'Net']);

            foreach ($daily as $row) {
                fputcsv($handle, [
                    $row->day,
                    $row->tickets,
                    $row->bookings,
                    $this->money($row->gross_cents),
                    $this->money($row->refund_cents),
                    $this->money($row->net_cents),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Sales by movie']);
            fputcsv($handle, ['Movie', 'Tickets', 'Bookings', 'Gross', 'Refunds', 'Net']);

            foreach ($perMovie as $row) {
                fputcsv($handle, [
                    $row->title,
                    $row->tickets,
                    $row->bookings,
                    $this->money($row->gross_cents),
                    $this->money($row->refund_cents),
                    $this->money($row->net_cents),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function bookingsExport(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);

        $filename = 'cinebook-bookings-'.$from->toDateString().'-to-'.$to->toDateString().'.csv';

        return response()->streamDownload(function () use ($from, $to) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Booking', 'Created', 'Customer', 'Email', 'Movie', 'Showtime', 'Screen', 'Seats', 'Status', 'Total', 'Payment', 'Refund']);

            Booking::with(['user', 'showtime.movie', 'showtime.screen', 'seats', 'payment'])
                ->whereBetween('created_at', [$from, $to])
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
                            $this->money($booking->total_amount_cents),
                            $booking->payment?->status,
                            $booking->payment?->refund_status,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array<string, int>
     */
    private function summary(Carbon $from, Carbon $to): array
    {
        $bookings = Booking::whereBetween('created_at', [$from, $to]);

        $gross = (int) Payment::where('status', 'successful')
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount_cents');

        $refunded = (int) Payment::where('refund_status', 'succeeded')
            ->whereBetween('created_at', [$from, $to])
            ->sum('refunded_amount_cents');

        $tickets = Ticket::whereBetween('tickets.created_at', [$from, $to])
            ->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
            ->whereIn('bookings.status', ['confirmed', 'refunded'])
            ->count();

        return [
            'bookings' => (clone $bookings)->count(),
            'confirmed' => (clone $bookings)->where('status', 'confirmed')->count(),
            'cancelled' => (clone $bookings)->whereIn('status', ['cancelled', 'refunded'])->count(),
            'tickets' => $tickets,
            'gross_cents' => $gross,
            'refunded_cents' => $refunded,
            'net_cents' => $gross - $refunded,
        ];
    }

    /**
     * @return Collection<int, object>
     */
    private function daily(Carbon $from, Carbon $to): Collection
    {
        $payments = Payment::where('status', 'successful')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('date(created_at) as day, count(*) as bookings, sum(amount_cents) as gross')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $refunds = Payment::where('refund_status', 'succeeded')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('date(created_at) as day, sum(refunded_amount_cents) as refunds')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $tickets = Ticket::whereBetween('tickets.created_at', [$from, $to])
            ->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
            ->whereIn('bookings.status', ['confirmed', 'refunded'])
            ->selectRaw('date(tickets.created_at) as day, count(*) as tickets')
            ->groupBy('day')
            ->pluck('tickets', 'day');

        $rows = collect();
        $cursor = $from->copy();

        while ($cursor->lte($to)) {
            $key = $cursor->toDateString();
            $gross = (int) ($payments[$key]->gross ?? 0);
            $refund = (int) ($refunds[$key]->refunds ?? 0);

            $rows->push((object) [
                'day' => $key,
                'tickets' => (int) ($tickets[$key] ?? 0),
                'bookings' => (int) ($payments[$key]->bookings ?? 0),
                'gross_cents' => $gross,
                'refund_cents' => $refund,
                'net_cents' => $gross - $refund,
            ]);

            $cursor->addDay();
        }

        return $rows;
    }

    /**
     * @return Collection<int, object>
     */
    private function perMovie(Carbon $from, Carbon $to): Collection
    {
        $tickets = DB::table('tickets')
            ->join('bookings', 'bookings.id', '=', 'tickets.booking_id')
            ->join('showtimes', 'showtimes.id', '=', 'bookings.showtime_id')
            ->whereBetween('tickets.created_at', [$from, $to])
            ->whereIn('bookings.status', ['confirmed', 'refunded'])
            ->selectRaw('showtimes.movie_id, count(*) as tickets')
            ->groupBy('showtimes.movie_id')
            ->pluck('tickets', 'movie_id');

        return Payment::query()
            ->join('bookings', 'bookings.id', '=', 'payments.booking_id')
            ->join('showtimes', 'showtimes.id', '=', 'bookings.showtime_id')
            ->join('movies', 'movies.id', '=', 'showtimes.movie_id')
            ->where('payments.status', 'successful')
            ->whereBetween('payments.created_at', [$from, $to])
            ->selectRaw('movies.id as movie_id, movies.title, count(distinct bookings.id) as bookings')
            ->selectRaw('sum(payments.amount_cents) as gross')
            ->selectRaw("sum(case when payments.refund_status = 'succeeded' then payments.refunded_amount_cents else 0 end) as refunds")
            ->groupBy('movies.id', 'movies.title')
            ->orderByDesc('gross')
            ->get()
            ->map(function ($row) use ($tickets) {
                $row->tickets = (int) ($tickets[$row->movie_id] ?? 0);
                $row->gross_cents = (int) $row->gross;
                $row->refund_cents = (int) $row->refunds;
                $row->net_cents = $row->gross_cents - $row->refund_cents;

                return $row;
            });
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
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
