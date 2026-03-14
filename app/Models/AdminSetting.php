<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AdminSetting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type'];

    /** Get a setting value with optional default */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("admin_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /** Set a setting value and bust cache */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("admin_setting_{$key}");
    }
}