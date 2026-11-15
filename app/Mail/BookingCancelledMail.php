<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingCancelledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        public Booking $booking,
        public string $reason = 'Your booking was cancelled and a full refund has been requested.',
    ) {
        $booking->loadMissing(['showtime.movie', 'showtime.screen']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Booking cancelled — '.$this->booking->showtime->movie->title);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.booking-cancelled', with: [
            'booking' => $this->booking,
            'reason' => $this->reason,
        ]);
    }
}
