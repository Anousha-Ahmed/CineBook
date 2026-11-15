<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $fillable = [
        'screen_id',
        'row',
        'number',
        'is_premium',
        'is_disabled',
    ];

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'is_premium' => 'boolean',
            'is_disabled' => 'boolean',
        ];
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
        return $this->belongsToMany(Booking::class);
    }

    public function getLabelAttribute(): string
    {
        return $this->row.$this->number;
    }
}
