<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PageSetting extends Model
{
    protected $fillable = [
        'whatsapp_number', 'admin_notification_email', 'pic_name',
        'bank_name', 'bank_account_name', 'bank_account_number', 'duitnow_qr_path', 'pricing',
    ];

    protected function casts(): array
    {
        return ['pricing' => 'array'];
    }

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'whatsapp_number' => config('services.sewolah.whatsapp', '601116946696'),
            'admin_notification_email' => config('services.sewolah.email', 'sewolah@gmail.com'),
        ]);
    }

    public function picName(): string
    {
        return $this->pic_name ?: 'Pasukan SEWOLAH';
    }

    public function hasBankDetails(): bool
    {
        return filled($this->bank_name) && filled($this->bank_account_number);
    }

    public function qrUrl(): ?string
    {
        return $this->duitnow_qr_path && Storage::disk('public')->exists($this->duitnow_qr_path)
            ? Storage::disk('public')->url($this->duitnow_qr_path)
            : null;
    }
}
