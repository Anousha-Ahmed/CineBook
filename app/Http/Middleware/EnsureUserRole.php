<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $allowed = in_array('admin', $roles, true) && $user->isAdmin()
            || in_array($user->role, $roles, true);

        abort_unless($allowed, 403, 'You do not have access to this area.');

        return $next($request);
    }
}
