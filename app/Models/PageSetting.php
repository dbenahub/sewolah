<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSetting extends Model
{
    protected $fillable = ['whatsapp_number', 'admin_notification_email'];

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'whatsapp_number' => config('services.sewolah.whatsapp', '601116946696'),
            'admin_notification_email' => config('services.sewolah.email', 'sewolah@gmail.com'),
        ]);
    }
}
