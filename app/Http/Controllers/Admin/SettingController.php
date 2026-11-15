<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\Request;

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

    public function update(Request $request)
    {
        $data = $request->validate([
            'hold_minutes' => ['required', 'integer', 'min:1', 'max:60'],
            'max_seats' => ['required', 'integer', 'min:1', 'max:12'],
            'cancel_cutoff_hours' => ['required', 'integer', 'min:0', 'max:48'],
            'premium_surcharge_cents' => ['required', 'integer', 'min:0', 'max:10000'],
            'currency' => ['required', 'string', 'in:usd,eur,gbp,pkr'],
        ]);

        foreach ($data as $key => $value) {
            SettingsService::put($key, $value);
        }

        return back()->with('success', 'Settings saved.');
    }
}
