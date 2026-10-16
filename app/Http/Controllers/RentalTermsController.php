<?php

namespace App\Http\Controllers;

use App\Models\PageSetting;
use App\Models\PixelSetting;
use Illuminate\Support\HtmlString;

class RentalTermsController extends Controller
{
    public function show()
    {
        return response()->view('layouts.corporate', [
            'slot' => new HtmlString(view('pages.rental-terms')->render()),
            'title' => 'Terma & Syarat Sewaan | SEWOLAH',
            'description' => 'Terma, syarat dan polisi sewaan kenderaan SEWOLAH: kelayakan, bayaran, deposit, pembatalan, minyak dan penggunaan.',
            'pixelSettings' => PixelSetting::current(),
            'pageSettings' => PageSetting::current(),
            'bodyClass' => 'page-terms',
            'withLivewire' => false,
        ]);
    }
}
