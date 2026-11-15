<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketScan extends Model
{
    protected $fillable = ['ticket_id', 'staff_id', 'token_hash', 'result'];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
