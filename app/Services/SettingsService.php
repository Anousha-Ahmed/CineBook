<?php

namespace App\Services;

use App\Models\Setting;

class SettingsService
{
    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache === null) {
            try {
                self::$cache = Setting::query()->pluck('value', 'key')->all();
            } catch (\Throwable $e) {
                self::$cache = [];
            }
        }

        return self::$cache;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::all()[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        self::$cache = null;
    }

    public static function forget(): void
    {
        self::$cache = null;
    }
}
