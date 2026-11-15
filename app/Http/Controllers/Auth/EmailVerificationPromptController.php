<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        if (! $request->user()->hasVerifiedEmail()) {
            return view('auth.verify-email');
        }

        $route = match ($request->user()->role) {
            'staff' => 'staff.scan',
            'admin' => 'admin.dashboard',
            default => 'bookings.index',
        };

        return redirect()->route($route)->with('success', 'Your email is already verified.');
    }
}
