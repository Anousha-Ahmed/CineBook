<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentFailedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 4;

    public array $backoff = [60, 300, 900];

    public function __construct(public Booking $booking, public string $reason = 'Your payment did not complete.')
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Payment not completed — CineBook');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.payment-failed', with: [
            'booking' => $this->booking,
            'reason' => $this->reason,
        ]);
    }
}
