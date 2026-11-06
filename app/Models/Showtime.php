<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Showtime extends Model
{
    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function screen()
    {
        return $this->belongsTo(Screen::class);
    }
}
