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
            ->when($request->query('action'), fn ($q, $action) => $q->where('action', $action))
            ->when($request->query('actor'), fn ($q, $actor) => $q->whereHas(
                'actor',
                fn ($a) => $a->where('email', 'like', "%{$actor}%")->orWhere('name', 'like', "%{$actor}%")
            ))
            ->when($request->query('from'), fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($request->query('to'), fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.audit.index', compact('logs', 'actions'));
    }
}
