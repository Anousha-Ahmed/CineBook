<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $home = match ($request->user()->role) {
            'staff' => route('staff.scan'),
            'admin' => route('admin.dashboard'),
            default => route('bookings.index'),
        };

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($home)
                ->with('success', 'Your email is already verified.');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended($home)
            ->with('success', 'Email verified — your account is ready.');
    }
}
