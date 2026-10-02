<?php

namespace App\Livewire\Public;

use App\Models\PageSetting;
use App\Models\PixelSetting;
use App\Models\Vehicle;
use App\Support\BookingPolicy;
use App\Support\CapturesUtm;
use Livewire\Component;

/**
 * Corporate main website (sewolah.com).
 */
class Home extends Component
{
    use CapturesUtm;

    public function mount(): void
    {
        $this->captureUtm();
    }

    public function render()
    {
        return view('livewire.public.home', [
            'vehicles' => Vehicle::where('is_active', true)->orderByDesc('is_featured')->orderBy('sort_order')->take(6)->get(),
            'minDays' => BookingPolicy::minWorkingDays(),
            'pageSettings' => PageSetting::current(),
        ])->layout('layouts.corporate', [
            'title' => __('home.meta_title'),
            'description' => __('home.meta_description'),
            'pixelSettings' => PixelSetting::current(),
            'pageSettings' => PageSetting::current(),
            'bodyClass' => 'page-home',
        ]);
    }
}
