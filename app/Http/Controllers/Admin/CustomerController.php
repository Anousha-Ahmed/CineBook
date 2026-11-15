<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->withCount('bookings')
            ->when($request->query('search'), function ($q, $search) {
                $q->where(fn ($inner) => $inner
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->query('role'), fn ($q, $role) => $q->where('role', $role))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', compact('users'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['customer', 'staff', 'admin'])],
        ]);

        if ($user->id === auth()->id() && $data['role'] !== 'admin') {
            return back()->with('error', 'You cannot remove your own admin access.');
        }

        if ($user->isAdmin() && $data['role'] !== 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'At least one admin account must remain.');
        }

        $before = $user->only(['role']);
        $user->update(['role' => $data['role']]);

        AuditLogger::log('user.role_changed', $user, $before, $user->only(['role']));

        return back()->with('success', "{$user->name} is now a {$data['role']}.");
    }
}
