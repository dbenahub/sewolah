<?php

namespace Database\Seeders;

use App\Models\PageSetting;
use Illuminate\Database\Seeder;

class PageSettingSeeder extends Seeder
{
    public function run(): void
    {
        PageSetting::updateOrCreate(['id' => 1], [
            'whatsapp_number' => env('SEWOLAH_WHATSAPP_NUMBER', '601116946696'),
            'admin_notification_email' => env('SEWOLAH_ADMIN_EMAIL', 'sewolah@gmail.com'),
        ]);
    }
}
