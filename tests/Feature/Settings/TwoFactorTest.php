<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Livewire\Settings\TwoFactor as TwoFactorComponent;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\mock;

it('can enable two factor and generate recovery codes', function () {
    config()->set('otp.enabled', false);
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(TwoFactorComponent::class)
        ->call('enableTwoFactorAuthentication')
        ->assertDispatched('two-factor-enabled');

    $user->refresh();

    expect($user->two_factor_secret)->not()->toBeNull();
    expect($user->two_factor_recovery_codes)->not()->toBeNull();
});

it('can confirm two factor with valid code', function () {
    config()->set('otp.enabled', false);
    $user = User::factory()->create();

    // Enable first
    actingAs($user);
    Livewire::test(TwoFactorComponent::class)->call('enableTwoFactorAuthentication');
    $user->refresh();

    // Mock provider to accept a given code
    $mock = mock(TwoFactorAuthenticationProvider::class);
    $mock->shouldReceive('verify')->andReturnTrue();
    $mock->shouldReceive('qrCodeUrl')->andReturn('<svg></svg>');

    Livewire::test(TwoFactorComponent::class)
        ->set('code', '123456')
        ->call('confirmTwoFactorAuthentication')
        ->assertDispatched('two-factor-confirmed');

    expect($user->refresh()->two_factor_confirmed_at)->not()->toBeNull();
});

it('regenerates recovery codes', function () {
    config()->set('otp.enabled', false);
    $user = User::factory()->create();

    actingAs($user);
    Livewire::test(TwoFactorComponent::class)->call('enableTwoFactorAuthentication');
    $user->refresh();
    $old = $user->two_factor_recovery_codes;

    Livewire::test(TwoFactorComponent::class)
        ->call('regenerateRecoveryCodes')
        ->assertDispatched('recovery-codes-regenerated');

    expect($user->refresh()->two_factor_recovery_codes)->not()->toEqual($old);
});

it('disables two factor', function () {
    config()->set('otp.enabled', false);
    $user = User::factory()->create();

    actingAs($user);
    Livewire::test(TwoFactorComponent::class)->call('enableTwoFactorAuthentication');

    Livewire::test(TwoFactorComponent::class)
        ->call('disableTwoFactorAuthentication')
        ->assertDispatched('two-factor-disabled');

    $u = $user->refresh();
    expect($u->two_factor_secret)->toBeNull();
    expect($u->two_factor_recovery_codes)->toBeNull();
    expect($u->two_factor_confirmed_at)->toBeNull();
});

it('prompts for two factor challenge on login when enabled', function () {
    config()->set('otp.enabled', false);
    $password = 'password-123';
    $user = User::factory()->create([
        'password' => Hash::make($password),
    ]);

    // Enable + confirm 2FA
    actingAs($user);
    Livewire::test(TwoFactorComponent::class)->call('enableTwoFactorAuthentication');
    // mock provider to make confirmation pass
    $mock = mock(TwoFactorAuthenticationProvider::class);
    $mock->shouldReceive('verify')->andReturnTrue();
    $mock->shouldReceive('qrCodeUrl')->andReturn('<svg></svg>');
    Livewire::test(TwoFactorComponent::class)->set('code', '123456')->call('confirmTwoFactorAuthentication');

    // Logout to simulate fresh login
    auth()->logout();

    // Attempt login and assert redirected to 2FA challenge
    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', $password)
        ->call('login')
        ->assertRedirect(route('two-factor.login', absolute: false));
});
