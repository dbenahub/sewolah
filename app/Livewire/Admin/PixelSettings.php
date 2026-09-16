<?php

namespace App\Livewire\Admin;

use App\Models\PixelSetting;
use Livewire\Component;

class PixelSettings extends Component
{
    public string $meta = '';
    public string $google = '';
    public string $tiktok = '';
    public bool $saved = false;

    public function mount()
    {
        $settings = PixelSetting::current();
        $this->meta = $settings->meta_pixel_id ?? '';
        $this->google = $settings->google_ads_id ?? '';
        $this->tiktok = $settings->tiktok_pixel_id ?? '';
    }

    public function save()
    {
        $settings = PixelSetting::current();
        $settings->update([
            'meta_pixel_id' => $this->meta ?: null,
            'google_ads_id' => $this->google ?: null,
            'tiktok_pixel_id' => $this->tiktok ?: null,
            'updated_by' => auth()->id(),
        ]);
        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.admin.pixel-settings')->layout('layouts.admin');
    }
}
