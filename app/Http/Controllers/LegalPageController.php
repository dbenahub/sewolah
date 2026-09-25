<?php

namespace App\Http\Controllers;

use App\Models\PageSetting;
use Illuminate\Contracts\View\View;

class LegalPageController extends Controller
{
    public function privacy(): View
    {
        return view('legal.privacy', ['pageSettings' => PageSetting::current()]);
    }

    public function terms(): View
    {
        return view('legal.terms', ['pageSettings' => PageSetting::current()]);
    }
}
