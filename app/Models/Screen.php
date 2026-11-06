<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Screen extends Model
{
    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function showtimes()
    {
        return $this->hasMany(Showtime::class);
    }
}
