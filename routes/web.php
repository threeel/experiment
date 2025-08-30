<?php

use App\Http\Controllers\AccountDeletionController;
use App\Livewire\Account\DeletionStatus;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'account.not-deleting'])
    ->name('dashboard');

Route::middleware(['auth', 'account.not-deleting'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    if (! config('otp.enabled')) {
        Route::get('settings/two-factor', \App\Livewire\Settings\TwoFactor::class)->name('settings.two-factor');
    }

    // Deletion status page for users marked for deletion
    Route::get('account/deletion-status', DeletionStatus::class)->name('account.deletion-status');
});

// Cancel deletion link (guest; use signed token-like string in DB)
Route::get('deletion/cancel/{token}', [AccountDeletionController::class, 'cancel'])->name('deletion.cancel');

// Two-factor recovery challenge page (guest), only if Fortify pre-login session exists
Route::get('two-factor-challenge/recovery', function () {
    if (! session()->has('login.id')) {
        return redirect()->route('login');
    }

    return view('auth.two-factor-recovery-challenge');
})->middleware(['web', 'guest:web'])->name('two-factor.login.recovery');

require __DIR__.'/auth.php';
