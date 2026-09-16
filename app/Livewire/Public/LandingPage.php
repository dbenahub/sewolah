<?php

namespace App\Livewire\Public;

use App\Models\PageSetting;
use App\Models\PixelSetting;
use App\Models\Vehicle;
use Livewire\Component;

class LandingPage extends Component
{
    public function mount()
    {
        // Capture UTM params on first visit and keep them for the booking form
        if (! session()->has('utm_captured')) {
            session([
                'utm' => [
                    'utm_source' => request('utm_source'),
                    'utm_medium' => request('utm_medium'),
                    'utm_campaign' => request('utm_campaign'),
                    'utm_content' => request('utm_content'),
                    'utm_term' => request('utm_term'),
                    'fbclid' => request('fbclid'),
                    'referrer' => request()->headers->get('referer'),
                    'landing_page_url' => request()->fullUrl(),
                ],
                'utm_captured' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.public.landing-page', [
            'vehicles' => Vehicle::where('is_active', true)->where('is_featured', true)->orderBy('sort_order')->get(),
            'pageSettings' => PageSetting::current(),
            'pixelSettings' => PixelSetting::current(),
        ])->layout('layouts.public');
    }
}
