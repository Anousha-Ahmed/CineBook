<?php

namespace App\Http\Controllers;

use App\Models\Ticket;

class TicketController extends Controller
{
    public function show(Ticket $ticket)
    {
        $ticket->load(['booking.showtime.movie', 'booking.showtime.screen', 'seat']);

        $this->authorize('view', $ticket);

        return view('tickets.show', compact('ticket'));
    }
}
