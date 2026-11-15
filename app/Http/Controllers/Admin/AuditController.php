<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('actor')
            ->when($request->query('search'), fn ($q, $search) => $q->where(function ($inner) use ($search) {
                $inner->where('action', 'like', "%{$search}%")
                    ->orWhere('target_type', 'like', "%{$search}%")
                    ->orWhereHas('actor', fn ($a) => $a->where('email', 'like', "%{$search}%"));
            }))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit.index', compact('logs'));
    }
}
