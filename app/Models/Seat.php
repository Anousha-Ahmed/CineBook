<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    public function screen()
    {
        return $this->belongsTo(Screen::class);
    }
}
