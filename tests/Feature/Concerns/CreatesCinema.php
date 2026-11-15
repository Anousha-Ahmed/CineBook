<?php

namespace Tests\Feature\Concerns;

use App\Models\Booking;
use App\Models\Movie;
use App\Models\Payment;
use App\Models\Screen;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\User;
use Illuminate\Support\Str;

trait CreatesCinema
{
    /**
     * @return array{0: Movie, 1: Screen, 2: Showtime, 3: \Illuminate\Support\Collection<int, Seat>}
     */
    protected function screening(array $showtimeOverrides = [], int $seatCount = 4): array
    {
        $movie = Movie::create([
            'title' => 'Test Movie '.Str::random(4),
            'synopsis' => 'A test movie.',
            'duration_minutes' => 120,
            'rating' => 'PG-13',
            'poster_url' => 'https://example.com/poster.jpg',
            'is_featured' => false,
        ]);

        $screen = Screen::create(['name' => 'Screen '.Str::random(4)]);

        $seats = collect(range(1, $seatCount))->map(fn (int $number) => Seat::create([
            'screen_id' => $screen->id,
            'row' => 'A',
            'number' => $number,
            'is_premium' => false,
            'is_disabled' => false,
        ]));

        $showtime = Showtime::create(array_merge([
            'movie_id' => $movie->id,
            'screen_id' => $screen->id,
            'start_time' => now()->addHours(3),
            'base_price_cents' => 1000,
            'status' => 'scheduled',
        ], $showtimeOverrides));

        return [$movie, $screen, $showtime, $seats];
    }

    protected function customer(string $role = 'customer'): User
    {
        $user = User::create([
            'name' => 'User '.Str::random(4),
            'email' => Str::random(8).'@example.com',
            'password' => 'password',
            'role' => $role,
        ]);

        $user->markEmailAsVerified();

        return $user;
    }

    /**
     * @param  array<int, Seat>  $seats
     */
    protected function booking(User $user, Showtime $showtime, array $seats, string $status = 'confirmed'): Booking
    {
        $booking = Booking::create([
            'user_id' => $user->id,
            'showtime_id' => $showtime->id,
            'status' => $status,
            'total_amount_cents' => count($seats) * $showtime->base_price_cents,
        ]);

        $booking->seats()->sync(collect($seats)->pluck('id')->all());

        Payment::create([
            'booking_id' => $booking->id,
            'amount_cents' => $booking->total_amount_cents,
            'status' => $status === 'confirmed' ? 'successful' : 'pending',
            'stripe_session_id' => 'cs_test_'.Str::random(12),
            'stripe_payment_intent' => 'pi_test_'.Str::random(12),
        ]);

        return $booking;
    }
}
