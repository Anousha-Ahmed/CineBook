<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketScan;

class TicketScanService
{
    /**
     * @return array{result: string, message: string, ticket: ?Ticket}
     */
    public function scan(string $rawToken, ?int $staffId = null, ?int $showtimeId = null): array
    {
        $token = trim($rawToken);
        $tokenHash = hash('sha256', $token);

        $ticket = Ticket::with(['booking.showtime.movie', 'booking.showtime.screen', 'seat'])
            ->where('token', $token)
            ->first();

        if (! $ticket) {
            $this->log(null, $staffId, $tokenHash, 'invalid');

            return ['result' => 'invalid', 'message' => 'Invalid ticket.', 'ticket' => null];
        }

        $booking = $ticket->booking;
        $showtime = $booking?->showtime;

        if (! $booking || ! $showtime) {
            $this->log($ticket, $staffId, $tokenHash, 'invalid');

            return ['result' => 'invalid', 'message' => 'Invalid ticket.', 'ticket' => $ticket];
        }

        if (in_array($booking->status, ['cancelled', 'refunded'], true)) {
            $this->log($ticket, $staffId, $tokenHash, 'cancelled');

            return [
                'result' => 'cancelled',
                'message' => 'Cancelled/Refunded.',
                'ticket' => $ticket,
            ];
        }

        if ($booking->status !== 'confirmed') {
            $this->log($ticket, $staffId, $tokenHash, 'invalid_booking');

            return [
                'result' => 'invalid_booking',
                'message' => 'This booking is not confirmed.',
                'ticket' => $ticket,
            ];
        }

        if ($showtimeId && (int) $showtime->id !== $showtimeId) {
            $this->log($ticket, $staffId, $tokenHash, 'wrong_showtime');

            return [
                'result' => 'wrong_showtime',
                'message' => 'Wrong showtime. This ticket is for '.$showtime->start_time->format('D j M, g:i A').' — '.$showtime->screen->name.'.',
                'ticket' => $ticket,
            ];
        }

        if ($ticket->status === 'used') {
            $this->log($ticket, $staffId, $tokenHash, 'already_used');

            return [
                'result' => 'already_used',
                'message' => 'Already used at '.($ticket->used_at?->format('g:i A') ?? 'an earlier scan').'.',
                'ticket' => $ticket,
            ];
        }

        if ($ticket->status !== 'valid' || $showtime->status !== 'scheduled') {
            $this->log($ticket, $staffId, $tokenHash, 'cancelled');

            return [
                'result' => 'cancelled',
                'message' => 'Cancelled/Refunded.',
                'ticket' => $ticket,
            ];
        }

        if (now()->lt($showtime->scanOpensAt())) {
            $this->log($ticket, $staffId, $tokenHash, 'too_early');

            return [
                'result' => 'too_early',
                'message' => 'Too early. Scanning opens at '.$showtime->scanOpensAt()->format('g:i A').'.',
                'ticket' => $ticket,
            ];
        }

        if (now()->gt($showtime->scanClosesAt())) {
            $this->log($ticket, $staffId, $tokenHash, 'expired');

            return [
                'result' => 'expired',
                'message' => 'Expired. The scanning window closed at '.$showtime->scanClosesAt()->format('g:i A').'.',
                'ticket' => $ticket,
            ];
        }

        // Atomic: two scanners checking the same QR cannot both admit it.
        $admitted = Ticket::whereKey($ticket->id)
            ->where('status', 'valid')
            ->update(['status' => 'used', 'used_at' => now()]);

        if (! $admitted) {
            $this->log($ticket, $staffId, $tokenHash, 'already_used');

            return [
                'result' => 'already_used',
                'message' => 'Already used at '.($ticket->fresh()->used_at?->format('g:i A') ?? 'an earlier scan').'.',
                'ticket' => $ticket->fresh(),
            ];
        }

        $this->log($ticket, $staffId, $tokenHash, 'valid');

        return [
            'result' => 'valid',
            'message' => 'Admitted.',
            'ticket' => $ticket->fresh(),
        ];
    }

    private function log(?Ticket $ticket, ?int $staffId, string $tokenHash, string $result): void
    {
        if (! $staffId) {
            return;
        }

        TicketScan::create([
            'ticket_id' => $ticket?->id,
            'staff_id' => $staffId,
            'token_hash' => $tokenHash,
            'result' => $result,
        ]);
    }
}
