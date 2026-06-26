<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getVal($key, $default = null)
    {
        try {
            return self::where('key', $key)->value('value') ?? $default;
        } catch (\Exception $e) {
            return $default;
        }
    }
}
