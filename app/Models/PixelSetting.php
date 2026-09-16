<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PixelSetting extends Model
{
    protected $fillable = ['meta_pixel_id', 'google_ads_id', 'tiktok_pixel_id', 'updated_by'];

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
