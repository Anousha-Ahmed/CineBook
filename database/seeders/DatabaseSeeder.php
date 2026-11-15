<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\Screen;
use App\Models\Seat;
use App\Models\Setting;
use App\Models\Showtime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $genres = $this->seedGenres();
        $screens = $this->seedScreensAndSeats();
        $this->seedMovies($genres);
        $this->seedShowtimes($screens);
        $this->seedSettings();
    }

    private function seedUsers(): void
    {
        $users = [
            ['name' => 'Admin', 'email' => 'admin@cinebook.com', 'role' => 'admin'],
            ['name' => 'Staff', 'email' => 'staff@cinebook.com', 'role' => 'staff'],
            ['name' => 'Alex Morgan', 'email' => 'customer@cinebook.com', 'role' => 'customer'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }

    private function seedGenres(): array
    {
        $genres = [];

        foreach (['Action', 'Comedy', 'Drama', 'Sci-Fi', 'Thriller', 'Horror', 'Adventure', 'Animation', 'Mystery', 'Family'] as $name) {
            $genres[$name] = Genre::firstOrCreate(['name' => $name]);
        }

        return $genres;
    }

    private function seedScreensAndSeats(): array
    {
        $screens = [];
        $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

        foreach (['Screen 1', 'Screen 2'] as $name) {
            $screen = Screen::firstOrCreate(['name' => $name]);
            $screens[] = $screen;

            foreach ($rows as $row) {
                for ($number = 1; $number <= 12; $number++) {
                    Seat::firstOrCreate(
                        ['screen_id' => $screen->id, 'row' => $row, 'number' => $number],
                        ['is_premium' => in_array($row, ['G', 'H'], true), 'is_disabled' => false]
                    );
                }
            }
        }

        return $screens;
    }

    private function seedMovies(array $genres): void
    {
        $movies = [
            [
                'title' => 'Oppenheimer',
                'synopsis' => 'The story of American scientist J. Robert Oppenheimer and his role in the development of the atomic bomb.',
                'duration_minutes' => 180,
                'rating' => 'R',
                'poster_url' => 'https://upload.wikimedia.org/wikipedia/en/4/4a/Oppenheimer_%28film%29.jpg',
                'genres' => ['Drama', 'Thriller'],
                'is_featured' => false,
            ],
            [
                'title' => 'Interstellar',
                'synopsis' => 'A team of explorers travel through a wormhole in space in an attempt to ensure humanity\'s survival.',
                'duration_minutes' => 169,
                'rating' => 'PG-13',
                'poster_url' => 'https://upload.wikimedia.org/wikipedia/en/b/bc/Interstellar_film_poster.jpg',
                'genres' => ['Sci-Fi', 'Drama'],
                'is_featured' => true,
            ],
            [
                'title' => 'Dune: Part Two',
                'synopsis' => 'Paul Atreides unites with Chani and the Fremen while seeking revenge against the conspirators who destroyed his family.',
                'duration_minutes' => 166,
                'rating' => 'PG-13',
                'poster_url' => 'https://upload.wikimedia.org/wikipedia/en/5/52/Dune_Part_Two_poster.jpeg',
                'genres' => ['Sci-Fi', 'Adventure'],
                'is_featured' => false,
            ],
            [
                'title' => 'Inside Out 2',
                'synopsis' => 'Riley enters her teenage years as new emotions arrive and turn her emotional world upside down.',
                'duration_minutes' => 96,
                'rating' => 'PG',
                'poster_url' => 'https://upload.wikimedia.org/wikipedia/en/f/f7/Inside_Out_2_poster.jpg',
                'genres' => ['Animation', 'Comedy', 'Family'],
                'is_featured' => false,
            ],
            [
                'title' => 'A Quiet Place: Day One',
                'synopsis' => 'A woman struggles to survive in a city invaded by terrifying creatures that hunt using sound.',
                'duration_minutes' => 99,
                'rating' => 'PG-13',
                'poster_url' => 'https://upload.wikimedia.org/wikipedia/en/5/5b/A_Quiet_Place_Day_One_poster.jpg',
                'genres' => ['Horror', 'Thriller'],
                'is_featured' => false,
            ],
            [
                'title' => 'Jawan',
                'synopsis' => 'A high-octane action thriller which outlines the emotional journey of a man who is set to rectify the wrongs in the society.',
                'duration_minutes' => 169,
                'rating' => 'PG-13',
                'poster_url' => 'https://upload.wikimedia.org/wikipedia/en/3/39/Jawan_film_poster.jpg',
                'genres' => ['Action', 'Thriller'],
                'is_featured' => false,
            ],
            [
                'title' => 'Animal',
                'synopsis' => 'The bond between a father and son is taken to the extreme as a son goes to any lengths to protect his father\'s legacy.',
                'duration_minutes' => 201,
                'rating' => 'R',
                'poster_url' => 'https://upload.wikimedia.org/wikipedia/en/9/90/Animal_%282023_film%29_poster.jpg',
                'genres' => ['Action', 'Drama'],
                'is_featured' => false,
            ],
            [
                'title' => 'Stree 2',
                'synopsis' => 'The town of Chanderi is haunted by a new threat, and the residents must once again rely on Stree to save them.',
                'duration_minutes' => 149,
                'rating' => 'PG-13',
                'poster_url' => 'https://upload.wikimedia.org/wikipedia/en/9/9b/Stree_2_poster.jpg',
                'genres' => ['Comedy', 'Horror'],
                'is_featured' => false,
            ],
            [
                'title' => 'Laapataa Ladies',
                'synopsis' => 'Two newly married brides get accidentally switched during a train journey, leading to a hilarious adventure.',
                'duration_minutes' => 124,
                'rating' => 'PG',
                'poster_url' => 'https://upload.wikimedia.org/wikipedia/en/9/9e/Laapataa_Ladies_poster.jpg',
                'genres' => ['Comedy', 'Drama'],
                'is_featured' => false,
            ],
            [
                'title' => 'Avatar: Fire and Ash',
                'synopsis' => 'Jake Sully and Neytiri face a new threat as the Ash People rise on Pandora. In cinemas soon.',
                'duration_minutes' => 190,
                'rating' => 'PG-13',
                'poster_url' => 'https://picsum.photos/seed/avatar-fire-and-ash/600/900',
                'genres' => ['Sci-Fi', 'Adventure'],
                'is_featured' => false,
            ],
            [
                'title' => 'Wicked: For Good',
                'synopsis' => 'The second chapter of the untold story of the witches of Oz. Coming soon.',
                'duration_minutes' => 138,
                'rating' => 'PG',
                'poster_url' => 'https://picsum.photos/seed/wicked-for-good/600/900',
                'genres' => ['Family', 'Adventure'],
                'is_featured' => false,
            ],
        ];

        foreach ($movies as $data) {
            $genreNames = $data['genres'];
            unset($data['genres']);

            $movie = Movie::updateOrCreate(['title' => $data['title']], $data);
            $movie->genres()->sync(
                collect($genreNames)->map(fn ($name) => $genres[$name]->id)->all()
            );
        }
    }

    private function seedShowtimes(array $screens): void
    {
        // Only clear showtimes that nobody has booked yet.
        Showtime::whereDoesntHave('bookings')->delete();

        $comingSoon = ['Avatar: Fire and Ash', 'Wicked: For Good'];

        $playing = Movie::whereNotIn('title', $comingSoon)->orderBy('id')->get();

        $times = [
            [14, 30, 1000],
            [18, 30, 1200],
            [21, 30, 1000],
        ];

        foreach ($playing as $index => $movie) {
            $screen = $screens[$index % count($screens)];

            foreach (range(0, 6) as $dayOffset) {
                foreach ($times as [$hour, $minute, $price]) {
                    $start = Carbon::today()->addDays($dayOffset)->setTime($hour, $minute);

                    if ($start->isPast()) {
                        continue;
                    }

                    Showtime::firstOrCreate(
                        [
                            'movie_id' => $movie->id,
                            'screen_id' => $screen->id,
                            'start_time' => $start,
                        ],
                        [
                            'base_price_cents' => $price,
                            'status' => 'scheduled',
                        ]
                    );
                }
            }
        }
    }

    private function seedSettings(): void
    {
        $defaults = [
            'hold_minutes' => 10,
            'max_seats' => 6,
            'cancel_cutoff_hours' => 2,
            'premium_surcharge_cents' => 200,
            'currency' => 'usd',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => (string) $value]);
        }
    }
}
