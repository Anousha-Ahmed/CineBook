<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\Seat;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $nowShowing = Movie::with('genres')
            ->withNextShowtime()
            ->whereHas('showtimes', fn ($q) => $q->onSale())
            ->orderByRaw('next_showtime_at IS NULL')
            ->orderBy('next_showtime_at')
            ->get();

        $comingSoon = Movie::with('genres')
            ->whereDoesntHave('showtimes', fn ($q) => $q->onSale())
            ->latest()
            ->take(4)
            ->get();

        $heroMovie = $nowShowing->firstWhere('is_featured', true) ?? $nowShowing->first();

        $dates = Showtime::onSale()
            ->orderBy('start_time')
            ->pluck('start_time')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->take(7)
            ->values();

        $selectedDate = $request->query('date');
        if (! $selectedDate || ! $dates->contains($selectedDate)) {
            $selectedDate = $dates->first();
        }

        $dayShowtimes = collect();
        if ($selectedDate) {
            $dayShowtimes = Showtime::with(['movie', 'screen'])
                ->onSale()
                ->whereDate('start_time', $selectedDate)
                ->orderBy('start_time')
                ->get()
                ->groupBy('movie_id');
        }

        $seatCounts = Seat::query()
            ->selectRaw('screen_id, count(*) as total')
            ->groupBy('screen_id')
            ->pluck('total', 'screen_id');

        $takenByShowtime = $this->takenSeatCounts($dayShowtimes->flatten()->pluck('id')->all());

        return view('home', compact(
            'nowShowing',
            'comingSoon',
            'heroMovie',
            'dates',
            'selectedDate',
            'dayShowtimes',
            'seatCounts',
            'takenByShowtime',
        ));
    }

    public function movies(Request $request)
    {
        $query = Movie::with('genres')->withNextShowtime();

        if ($search = $request->query('search')) {
            $query->search($search);
        }

        if ($genreId = $request->query('genre')) {
            $query->whereHas('genres', fn ($q) => $q->where('genres.id', $genreId));
        }

        $filter = $request->query('filter');

        if ($filter === 'now-showing') {
            $query->whereHas('showtimes', fn ($q) => $q->onSale());
        } elseif ($filter === 'coming-soon') {
            $query->whereDoesntHave('showtimes', fn ($q) => $q->onSale());
        }

        $movies = $query->orderBy('title')->paginate(12)->withQueryString();
        $genres = Genre::orderBy('name')->get();

        return view('movies.index', compact('movies', 'genres', 'search', 'filter', 'genreId'));
    }

    public function show(Movie $movie)
    {
        $movie->load('genres');

        $showtimes = $movie->showtimes()
            ->with('screen')
            ->onSale()
            ->orderBy('start_time')
            ->get();

        $byDate = $showtimes->groupBy(fn ($s) => $s->start_time->toDateString());

        $seatCounts = Seat::query()
            ->selectRaw('screen_id, count(*) as total')
            ->groupBy('screen_id')
            ->pluck('total', 'screen_id');

        $taken = $this->takenSeatCounts($showtimes->pluck('id')->all());

        $related = Movie::with('genres')
            ->whereKeyNot($movie->id)
            ->whereHas('genres', fn ($q) => $q->whereIn('genres.id', $movie->genres->pluck('id')))
            ->take(5)
            ->get();

        if ($related->count() < 4) {
            $related = $related->merge(
                Movie::with('genres')->whereKeyNot($movie->id)->take(5)->get()
            )->unique('id')->take(5);
        }

        return view('movies.show', compact('movie', 'byDate', 'seatCounts', 'taken', 'related'));
    }

    private function takenSeatCounts(array $showtimeIds): array
    {
        if ($showtimeIds === []) {
            return [];
        }

        $sold = DB::table('booking_seat')
            ->join('bookings', 'bookings.id', '=', 'booking_seat.booking_id')
            ->whereIn('bookings.showtime_id', $showtimeIds)
            ->whereIn('bookings.status', ['confirmed', 'pending_payment'])
            ->selectRaw('bookings.showtime_id as showtime_id, count(*) as total')
            ->groupBy('bookings.showtime_id')
            ->pluck('total', 'showtime_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $held = DB::table('seat_holds')
            ->whereIn('showtime_id', $showtimeIds)
            ->where('expires_at', '>', now())
            ->selectRaw('showtime_id, count(*) as total')
            ->groupBy('showtime_id')
            ->pluck('total', 'showtime_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $result = [];
        foreach (array_unique(array_merge(array_keys($sold), array_keys($held))) as $id) {
            $result[$id] = ($sold[$id] ?? 0) + ($held[$id] ?? 0);
        }

        return $result;
    }
}
