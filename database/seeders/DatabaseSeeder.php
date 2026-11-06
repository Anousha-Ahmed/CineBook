<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Screen;
use App\Models\Seat;
use App\Models\Showtime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin User
        User::create([
            'name' => 'Admin',
            'email' => 'admin@cinebook.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // 2. Staff User
        User::create([
            'name' => 'Staff',
            'email' => 'staff@cinebook.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        // 3. Genres
        $genres = ['Action', 'Comedy', 'Drama', 'Sci-Fi', 'Horror'];
        foreach ($genres as $genre) {
            Genre::create(['name' => $genre]);
        }

        // 4. Screens 
        $screen1 = Screen::create(['name' => 'Screen 1']);
        $screen2 = Screen::create(['name' => 'Screen 2']);

        // 5. Seats (Grid generator: 5 rows x 8 seats = 40 seats)
        $rows = ['A', 'B', 'C', 'D', 'E'];
        foreach ([$screen1, $screen2] as $screen) {
            foreach ($rows as $row) {
                for ($num = 1; $num <= 8; $num++) {
                    Seat::create([
                        'screen_id' => $screen->id,
                        'row' => $row,
                        'number' => $num,
                        'is_premium' => in_array($row, ['D', 'E']), // D aur E rows premium hain
                        'is_disabled' => false,
                    ]);
                }
            }
        }

        // 6. Movies
        $movie1 = Movie::create([
            'title' => 'Dune: Part Two',
            'synopsis' => 'Paul Atreides unites with Chani and the Fremen while seeking revenge against the conspirators who destroyed his family.',
            'duration_minutes' => 166,
            'rating' => 'PG-13',
            'poster_url' => 'https://example.com/dune.jpg',
        ]);
        
        $movie2 = Movie::create([
            'title' => 'Deadpool & Wolverine',
            'synopsis' => 'Wolverine is recovering from his injuries when he crosses paths with the loudmouth, Deadpool. They team up to defeat a common enemy.',
            'duration_minutes' => 128,
            'rating' => 'R',
            'poster_url' => 'https://example.com/deadpool.jpg',
        ]);

        // Attach genres to movies
        $movie1->genres()->attach([1, 4]); // Action & Sci-Fi
        $movie2->genres()->attach([1, 2]); // Action & Comedy

        // 7. Showtimes 
        Showtime::create([
            'movie_id' => $movie1->id,
            'screen_id' => $screen1->id,
            'start_time' => Carbon::now()->addHours(5), 
            'base_price_cents' => 1500, 
        ]);

        Showtime::create([
            'movie_id' => $movie2->id,
            'screen_id' => $screen2->id,
            'start_time' => Carbon::now()->addDays(1)->setTime(20, 0), 
            'base_price_cents' => 1200, 
        ]);
    }
}