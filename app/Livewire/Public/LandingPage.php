<?php

namespace App\Livewire\Public;

use App\Models\PageSetting;
use App\Models\PixelSetting;
use App\Models\Vehicle;
use App\Support\CapturesUtm;
use Livewire\Component;

class LandingPage extends Component
{
    use CapturesUtm;

    public function mount()
    {
        // Capture UTM params on first visit and keep them for the booking form
        $this->captureUtm();
    }

    public function render()
    {
        return view('livewire.public.landing-page', [
            'vehicles' => Vehicle::where('is_active', true)->where('is_featured', true)->orderBy('sort_order')->get(),
            'pageSettings' => PageSetting::current(),
            'pixelSettings' => PixelSetting::current(),
        ])->layout('layouts.public', [
            'pixelSettings' => PixelSetting::current(),
        ]);
    }
}
