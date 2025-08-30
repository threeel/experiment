<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    if (! config('otp.enabled')) {
        Route::get('settings/two-factor', \App\Livewire\Settings\TwoFactor::class)->name('settings.two-factor');
    }
});

// Two-factor recovery challenge page (guest), only if Fortify pre-login session exists
Route::get('two-factor-challenge/recovery', function () {
    if (! session()->has('login.id')) {
        return redirect()->route('login');
    }

    return view('auth.two-factor-recovery-challenge');
})->middleware(['web', 'guest:web'])->name('two-factor.login.recovery');

require __DIR__.'/auth.php';
