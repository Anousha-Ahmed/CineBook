<?php

namespace App\Mail;

use App\Models\Booking;
use App\Support\Qr;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    /**
     * @var list<array{seat: string, token: string, qr: string}>
     */
    public array $ticketCodes = [];

    public function __construct(public Booking $booking)
    {
        $booking->loadMissing(['tickets.seat', 'showtime.movie', 'showtime.screen']);

        $this->ticketCodes = $booking->tickets->map(fn ($ticket) => [
            'seat' => $ticket->seat?->label ?? '—',
            'token' => $ticket->token,
            'qr' => Qr::dataUri($ticket->token),
        ])->all();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your CineBook tickets — '.$this->booking->showtime->movie->title);
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.booking-confirmed',
            with: [
                'booking' => $this->booking,
                'tickets' => $this->ticketCodes,
            ],
        );
    }
}
