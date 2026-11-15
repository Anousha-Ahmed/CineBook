<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>Payment not completed</title></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;">
                    <tr><td style="background:#0b1120;padding:24px 28px;"><span style="font-size:20px;font-weight:bold;color:#ffffff;">Cine<span style="color:#60a5fa;">Book</span></span></td></tr>
                    <tr>
                        <td style="padding:28px;">
                            <h1 style="margin:0 0 10px;font-size:22px;">Payment not completed</h1>
                            <p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#475569;">{{ $reason }}</p>
                            <p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#475569;">
                                {{ $booking->showtime->movie->title }} &middot; {{ $booking->showtime->start_time->format('l j F, g:i A') }}
                                &middot; {{ $booking->showtime->screen->name }}
                            </p>
                            <p style="margin:0;font-size:14px;line-height:1.6;color:#475569;">
                                Your seats have been released. You can pick new seats any time while sales are open.
                            </p>
                            <p style="margin:24px 0 0;">
                                <a href="{{ route('movies.index') }}" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:10px;font-size:14px;font-weight:bold;">Browse showtimes</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
