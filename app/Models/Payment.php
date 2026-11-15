<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'stripe_session_id',
        'stripe_payment_intent',
        'stripe_refund_id',
        'amount_cents',
        'refunded_amount_cents',
        'status',
        'refund_status',
        'failure_reason',
        'refund_attempts',
    ];

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'refunded_amount_cents' => 'integer',
            'refund_attempts' => 'integer',
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'successful';
    }

    public function isRefunded(): bool
    {
        return $this->refund_status === 'succeeded';
    }
}
