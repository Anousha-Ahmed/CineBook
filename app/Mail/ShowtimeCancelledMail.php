<?php

namespace App\Mail;

use App\Models\Showtime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShowtimeCancelledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 4;

    public array $backoff = [60, 300, 900];

    public function __construct(public Showtime $showtime)
    {
        $showtime->loadMissing(['movie', 'screen']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Showtime cancelled — '.$this->showtime->movie->title);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.showtime-cancelled', with: ['showtime' => $this->showtime]);
    }
}
