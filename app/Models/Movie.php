<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    public function genres()
    {
         return $this->belongsToMany(Genre::class, 'movie_genre');
    }

    public function showtimes()
    {
        return $this->hasMany(Showtime::class);
    }
}
