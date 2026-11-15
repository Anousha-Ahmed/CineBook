<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class FailedJobController extends Controller
{
    public function index()
    {
        $jobs = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->paginate(20);

        return view('admin.failed-jobs.index', compact('jobs'));
    }

    public function retry(string $uuid)
    {
        $job = DB::table('failed_jobs')->where('uuid', $uuid)->first();

        abort_unless($job, 404);

        Artisan::call('queue:retry', ['id' => [$uuid]]);

        AuditLogger::log('failed_job.retried', null, [], ['uuid' => $uuid], ['queue' => $job->queue]);

        return back()->with('success', 'The job was pushed back onto the queue.');
    }

    public function destroy(string $uuid)
    {
        $deleted = DB::table('failed_jobs')->where('uuid', $uuid)->delete();

        abort_unless($deleted, 404);

        AuditLogger::log('failed_job.deleted', null, ['uuid' => $uuid]);

        return back()->with('success', 'The failed job was removed.');
    }
}
