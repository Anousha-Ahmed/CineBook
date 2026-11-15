<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Your CineBook tickets</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="background:#0b1120;padding:24px 28px;">
                            <span style="font-size:20px;font-weight:bold;color:#ffffff;">Cine<span style="color:#60a5fa;">Book</span></span>
                            <p style="margin:4px 0 0;font-size:12px;color:#94a3b8;">Downtown cinema</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <h1 style="margin:0 0 8px;font-size:22px;color:#0f172a;">You're booked, {{ $booking->user->name }}.</h1>
                            <p style="margin:0 0 20px;font-size:14px;line-height:1.6;color:#475569;">
                                Payment confirmed. Show this email at the entrance — each ticket has its own QR code.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border-radius:12px;padding:16px;">
                                <tr>
                                    <td style="padding:16px;">
                                        <p style="margin:0;font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#64748b;">Screening</p>
                                        <p style="margin:6px 0 0;font-size:16px;font-weight:bold;color:#0f172a;">{{ $booking->showtime->movie->title }}</p>
                                        <p style="margin:4px 0 0;font-size:13px;color:#475569;">
                                            {{ $booking->showtime->start_time->format('l j F, g:i A') }} &middot; {{ $booking->showtime->screen->name }}
                                        </p>
                                        <p style="margin:8px 0 0;font-size:13px;color:#475569;">Booking #{{ $booking->id }} &middot; {{ $booking->tickets->count() }} ticket(s)</p>
                                    </td>
                                </tr>
                            </table>

                            @foreach ($tickets as $ticket)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:16px;border:1px solid #e2e8f0;border-radius:12px;">
                                    <tr>
                                        <td style="padding:16px;vertical-align:middle;">
                                            <p style="margin:0;font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#64748b;">Seat</p>
                                            <p style="margin:4px 0 0;font-size:20px;font-weight:bold;color:#0f172a;">{{ $ticket['seat'] }}</p>
                                            <p style="margin:8px 0 0;font-size:11px;color:#94a3b8;font-family:monospace;word-break:break-all;">{{ $ticket['token'] }}</p>
                                        </td>
                                        <td style="padding:12px 16px;text-align:right;vertical-align:middle;">
                                            <img src="{{ $ticket['qr'] }}" alt="Ticket QR code" width="140" height="140" style="display:inline-block;border-radius:8px;">
                                        </td>
                                    </tr>
                                </table>
                            @endforeach

                            <p style="margin:24px 0 0;font-size:13px;line-height:1.6;color:#475569;">
                                Doors open {{ \App\Support\BookingRules::SCAN_OPEN_MINUTES }} minutes before the show. Tickets cannot be refunded within
                                {{ \App\Services\SettingsService::get('cancel_cutoff_hours', 2) }} hours of the start time or after they are scanned.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f8fafc;padding:18px 28px;font-size:11px;color:#94a3b8;text-align:center;">
                            CineBook &middot; 120 Market Street &middot; This is an automated message.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
