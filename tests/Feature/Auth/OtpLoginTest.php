<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('sends an OTP to existing user email when user has OTP enabled', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'test@example.com', 'otp_enabled' => true, 'otp_driver' => 'email']);

    Livewire::test(Login::class)
        ->set('email', 'test@example.com')
        ->call('login')
        ->assertSet('otpSent', true)
        ->assertSee(trans('If an account exists for that email, we have sent a login code.'));

    // Code exists in cache
    expect(Cache::has('otp:login:test@example.com'))->toBeTrue();
});

it('logs in a user after verifying correct OTP code', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'test2@example.com', 'otp_enabled' => true, 'otp_driver' => 'email']);

    // Step 1: request code
    $component = Livewire::test(Login::class)
        ->set('email', 'test2@example.com')
        ->call('login');

    // Grab code from cache
    $cacheKey = 'otp:login:test2@example.com';
    $code = Cache::get($cacheKey);
    expect($code)->not()->toBeNull();

    // Step 2: verify code
    $component
        ->set('code', $code)
        ->set('remember', true)
        ->call('login')
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid or expired code', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'bad@example.com', 'otp_enabled' => true, 'otp_driver' => 'email']);

    Livewire::test(Login::class)
        ->set('email', 'bad@example.com')
        ->call('login')
        ->set('code', '000000')
        ->call('login')
        ->assertHasErrors(['code' => null]);

    $this->assertGuest();
});

it('supports resending code after throttle with countdown state', function () {
    config()->set('otp.throttle_seconds', 5);

    Mail::fake();

    User::factory()->create(['email' => 'again@example.com', 'otp_enabled' => true, 'otp_driver' => 'email']);

    $component = Livewire::test(Login::class)
        ->set('email', 'again@example.com')
        ->call('login')
        ->assertSet('otpSent', true);

    // Immediately after send, resend should be throttled; countdown set
    $component->assertSet('resendIn', 5);

    // Simulate time passing by clearing the throttle cache key
    Cache::forget('otp:throttle:again@example.com');

    // Call resend
    $component->call('resendCode')
        ->assertSet('resendIn', 5)
        ->assertSee(trans('If an account exists for that email, we have sent a new login code.'));

    // Ensure a code exists
    expect(Cache::has('otp:login:again@example.com'))->toBeTrue();
});
