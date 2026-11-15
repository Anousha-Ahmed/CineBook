<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'showtime_id',
        'status',
        'total_amount_cents',
    ];

    protected function casts(): array
    {
        return [
            'total_amount_cents' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function showtime()
    {
        return $this->belongsTo(Showtime::class);
    }

    public function seats()
    {
        return $this->belongsToMany(Seat::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function hasUsedTickets(): bool
    {
        return $this->tickets()->where('status', 'used')->exists();
    }

    public function isCancellable(): bool
    {
        if (! $this->isConfirmed()) {
            return false;
        }

        if ($this->hasUsedTickets()) {
            return false;
        }

        $cutoff = (int) \App\Services\SettingsService::get('cancel_cutoff_hours', 2);

        return $this->showtime->start_time->gt(now()->addHours($cutoff));
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending_payment' => 'Pending payment',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired',
            'refunded' => 'Refunded',
            'failed' => 'Failed',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function statusClasses(): string
    {
        return match ($this->status) {
            'confirmed' => 'badge badge-green',
            'pending_payment' => 'badge badge-amber',
            'cancelled' => 'badge badge-slate',
            'refunded' => 'badge badge-blue',
            default => 'badge badge-red',
        };
    }
}
