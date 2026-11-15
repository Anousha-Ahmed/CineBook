<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Screen extends Model
{
    protected $fillable = ['name', 'rows', 'seats_per_row', 'premium_rows'];

    protected function casts(): array
    {
        return [
            'rows' => 'integer',
            'seats_per_row' => 'integer',
            'premium_rows' => 'integer',
        ];
    }

    public function seats()
    {
        return $this->hasMany(Seat::class)->orderBy('row')->orderBy('number');
    }

    public function showtimes()
    {
        return $this->hasMany(Showtime::class);
    }

    public function hasFutureBookings(): bool
    {
        return $this->showtimes()
            ->where('start_time', '>', now())
            ->whereHas('bookings', fn ($q) => $q->whereIn('status', ['confirmed', 'pending_payment']))
            ->exists();
    }
}
