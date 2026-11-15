<?php

namespace App\Models;

use App\Support\BookingRules;
use Illuminate\Database\Eloquent\Model;

class Showtime extends Model
{
    protected $fillable = [
        'movie_id',
        'screen_id',
        'start_time',
        'base_price_cents',
        'status',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'base_price_cents' => 'integer',
            'cancelled_at' => 'datetime',
        ];
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function screen()
    {
        return $this->belongsTo(Screen::class);
    }

    public function holds()
    {
        return $this->hasMany(SeatHold::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')->where('start_time', '>', now());
    }

    public function scopeOnSale($query)
    {
        return $query->where('status', 'scheduled')
            ->where('start_time', '>', now()->addMinutes(BookingRules::SALES_CUTOFF_MINUTES));
    }

    public function endsAt()
    {
        return $this->start_time->copy()->addMinutes($this->movie->duration_minutes);
    }

    public function salesCloseAt()
    {
        return $this->start_time->copy()->subMinutes(BookingRules::SALES_CUTOFF_MINUTES);
    }

    public function isOnSale(): bool
    {
        return $this->status === 'scheduled' && now()->lt($this->salesCloseAt());
    }

    public function scanOpensAt()
    {
        return $this->start_time->copy()->subMinutes(BookingRules::SCAN_OPEN_MINUTES);
    }

    public function scanClosesAt()
    {
        return $this->start_time->copy()->addMinutes(BookingRules::SCAN_CLOSE_MINUTES);
    }
}
