<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Services\AuditLogger;
use App\Services\SettingsService;

class SettingController extends Controller
{
    private const DEFAULTS = [
        'hold_minutes' => 10,
        'max_seats' => 6,
        'cancel_cutoff_hours' => 2,
        'premium_surcharge_cents' => 200,
        'currency' => 'usd',
    ];

    public function edit()
    {
        $settings = [];
        foreach (self::DEFAULTS as $key => $default) {
            $settings[$key] = SettingsService::get($key, $default);
        }

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(SettingsRequest $request)
    {
        $data = $request->validated();

        $before = [];
        foreach (array_keys(self::DEFAULTS) as $key) {
            $before[$key] = SettingsService::get($key, self::DEFAULTS[$key]);
        }

        $after = array_map(fn ($value) => (string) $value, $data);

        foreach ($data as $key => $value) {
            SettingsService::put($key, $value);
        }

        AuditLogger::log('settings.updated', null, $before, $after);

        return back()->with('success', 'Settings saved.');
    }
}
