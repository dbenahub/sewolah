<?php

namespace App\Http\Controllers;

use App\Models\PageSetting;
use App\Models\PixelSetting;
use App\Models\Vehicle;
use App\Support\BookingPolicy;
use App\Support\CapturesUtm;
use App\Support\Img;
use Illuminate\Support\HtmlString;

/**
 * Corporate main website (sewolah.com).
 *
 * Rendered as a plain Blade page (no Livewire) so the homepage ships no
 * framework JavaScript — it only needs to paint fast for ad traffic.
 */
class HomeController extends Controller
{
    use CapturesUtm;

    public function __invoke()
    {
        $this->captureUtm();

        $pageSettings = PageSetting::current();
        $hero = Img::opt('home-hero', [640, 960, 1280, 1536], 1280, 'images/home/hero.jpg');

        $content = view('livewire.public.home', [
            'vehicles' => Vehicle::where('is_active', true)->orderByDesc('is_featured')->orderBy('sort_order')->take(6)->get(),
            'minDays' => BookingPolicy::minWorkingDays(),
            'pageSettings' => $pageSettings,
            'heroImage' => $hero,
        ])->render();

        return response()->view('layouts.corporate', [
            'slot' => new HtmlString($content),
            'title' => __('home.meta_title'),
            'description' => __('home.meta_description'),
            'pixelSettings' => PixelSetting::current(),
            'pageSettings' => $pageSettings,
            'bodyClass' => 'page-home',
            'withLivewire' => false,
            'preloadImage' => $hero,
        ]);
    }
}
