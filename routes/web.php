<?php

use App\Livewire\Admin\BookingsTable;
use App\Livewire\Admin\ChangePassword;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\PageSettings;
use App\Livewire\Admin\PixelSettings;
use App\Livewire\Admin\VehicleManager;
use App\Livewire\Public\LandingPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::get('/', LandingPage::class)->name('landing');

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
        Route::get('/pixel-settings', PixelSettings::class)->name('pixel-settings');
        Route::get('/page-settings', PageSettings::class)->name('page-settings');
        Route::get('/vehicles', VehicleManager::class)->name('vehicles');
        Route::get('/change-password', ChangePassword::class)->name('change-password');
    });
});
