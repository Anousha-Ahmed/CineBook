<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = [
        'title',
        'synopsis',
        'duration_minutes',
        'rating',
        'poster_url',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'movie_genre');
    }

    public function showtimes()
    {
        return $this->hasMany(Showtime::class);
    }

    public function upcomingShowtimes()
    {
        return $this->showtimes()
            ->where('status', 'scheduled')
            ->where('start_time', '>', now())
            ->orderBy('start_time');
    }

    public function scopeWithNextShowtime($query)
    {
        return $query->withMin(
            ['showtimes as next_showtime_at' => fn ($q) => $q->where('status', 'scheduled')->where('start_time', '>', now())],
            'start_time'
        );
    }

    public function scopeSearch($query, ?string $term)
    {
        return $query->when($term, function ($q) use ($term) {
            $q->where(function ($inner) use ($term) {
                $inner->where('title', 'like', "%{$term}%")
                    ->orWhere('synopsis', 'like', "%{$term}%");
            });
        });
    }
}
