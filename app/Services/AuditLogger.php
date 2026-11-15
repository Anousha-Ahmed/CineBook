<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @param  array<string, mixed>  $meta
     */
    public static function log(
        string $action,
        ?Model $target = null,
        array $before = [],
        array $after = [],
        array $meta = [],
    ): AuditLog {
        return AuditLog::create([
            'actor_id' => auth()->id(),
            'action' => $action,
            'target_type' => $target ? $target::class : null,
            'target_id' => $target?->getKey(),
            'before' => $before ?: null,
            'after' => $after ?: null,
            'meta' => $meta ?: null,
            'ip' => request()?->ip(),
        ]);
    }
}
