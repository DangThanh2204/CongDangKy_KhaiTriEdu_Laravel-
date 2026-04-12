<?php

namespace App\Models;

use App\Models\MongoModel as Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    private const CACHE_KEY = 'settings.all';

    private static ?array $cachedSettings = null;

    protected $fillable = ['key', 'value'];

    public $timestamps = true;

    protected static function booted(): void
    {
        static::saved(fn () => self::flushCache());
        static::deleted(fn () => self::flushCache());
    }

    public static function get($key, $default = null)
    {
        return self::getMany([$key => $default])[$key];
    }

    public static function getMany(array $defaults): array
    {
        $settings = self::allCached();
        $values = [];

        foreach ($defaults as $key => $default) {
            $values[$key] = $settings[$key] ?? $default;
        }

        return $values;
    }

    public static function set($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public static function flushCache(): void
    {
        self::$cachedSettings = null;
        Cache::forget(self::CACHE_KEY);
    }

    private static function allCached(): array
    {
        if (self::$cachedSettings !== null) {
            return self::$cachedSettings;
        }

        self::$cachedSettings = Cache::rememberForever(self::CACHE_KEY, function () {
            return self::query()
                ->pluck('value', 'key')
                ->all();
        });

        return self::$cachedSettings;
    }
}
