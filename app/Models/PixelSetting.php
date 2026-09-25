<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PixelSetting extends Model
{
    protected $fillable = ['meta_pixel_id', 'google_ads_id', 'tiktok_pixel_id', 'updated_by'];

    public static function current(): self
    {
        $settings = static::firstOrCreate(['id' => 1]);

        if (blank($settings->meta_pixel_id) && filled(config('services.meta.pixel_id'))) {
            $settings->meta_pixel_id = (string) config('services.meta.pixel_id');
        }

        return $settings;
    }
}
