<?php

use App\Livewire\Admin\BookingsTable;
use App\Livewire\Admin\ChangePassword;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\PageSettings;
use App\Livewire\Admin\PixelSettings;
use App\Livewire\Admin\VehicleManager;
use App\Livewire\Public\LandingPage;
use App\Livewire\Public\RequestForm;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalPageController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
// Corporate main website
Route::get('/', HomeController::class)->name('home');

// Standalone booking application form (shareable link, e.g. via WhatsApp)
Route::get('/form', RequestForm::class)->name('booking.form');
Route::redirect('/borang', '/form', 301);
Route::redirect('/tempah', '/form', 301);

// Outstation / airport landing page (previously the homepage)
Route::get('/outstation', LandingPage::class)->name('landing');

// Customer booking portal: eligibility check, T&C, payment & status
Route::get('/tempahan/{token}', \App\Livewire\Public\BookingPortal::class)
    ->where('token', '[A-Za-z0-9]{32,64}')
    ->middleware('throttle:60,1')
    ->name('booking.portal');
Route::get('/terma-sewaan', [\App\Http\Controllers\RentalTermsController::class, 'show'])->name('rental.terms');

// One-tap actions from the PIC email (signed links)
Route::get('/pic/tempahan/{lead}/{action}', [\App\Http\Controllers\PicActionController::class, 'whatsapp'])
    ->middleware(['signed', 'throttle:30,1'])
    ->name('pic.whatsapp');

Route::get('/privacy-policy', [LegalPageController::class, 'privacy'])->name('privacy-policy');
Route::get('/terms', [LegalPageController::class, 'terms'])->name('terms');

Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['ms', 'en'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

/*
|--------------------------------------------------------------------------
| Admin auth
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Admin\AuthController::class, 'showLogin'])
        ->middleware('guest')->name('login');
    Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])
        ->middleware('guest')->name('login.attempt');
    Route::post('/logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])
        ->middleware('auth')->name('logout');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');
        Route::get('/bookings', BookingsTable::class)->name('bookings');
        Route::get('/bookings/{lead}', \App\Livewire\Admin\BookingManager::class)->name('bookings.manage');
        Route::get('/bookings/{lead}/dokumen/{document}', [\App\Http\Controllers\Admin\LeadDocumentController::class, 'show'])->name('bookings.document');
        Route::get('/harga-kereta', \App\Livewire\Admin\CarRates::class)->name('car-rates');
        Route::get('/tetapan-bayaran', \App\Livewire\Admin\PaymentSettings::class)->name('payment-settings');
        Route::get('/pixel-settings', PixelSettings::class)->name('pixel-settings');
        Route::get('/page-settings', PageSettings::class)->name('page-settings');
        Route::get('/vehicles', VehicleManager::class)->name('vehicles');
        Route::get('/change-password', ChangePassword::class)->name('change-password');
    });
});
