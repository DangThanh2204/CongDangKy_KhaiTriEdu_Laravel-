<?php

namespace App\Models;

use App\Models\MongoModel as Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];
    public $timestamps = true;

    /**
     * Láº¥y giÃ¡ trá»‹ setting theo key
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * LÆ°u hoáº·c cáº­p nháº­t setting
     */
    public static function set($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
