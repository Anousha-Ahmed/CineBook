<?php

namespace App\Http\Controllers;

use App\Models\Ticket;

class TicketController extends Controller
{
    public function show(Ticket $ticket)
    {
        $ticket->load(['booking.showtime.movie', 'booking.showtime.screen', 'seat']);

        $isOwner = $ticket->booking->user_id === auth()->id();

        abort_unless($isOwner || auth()->user()->isStaff(), 403);

        return view('tickets.show', compact('ticket'));
    }
}
