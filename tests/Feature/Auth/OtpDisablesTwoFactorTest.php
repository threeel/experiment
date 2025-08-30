<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Models\User;
use Livewire\Livewire;

it('does not trigger Fortify two-factor challenge when OTP is enabled', function () {
    config()->set('otp.enabled', true);

    $user = User::factory()->create([
        'password' => bcrypt('password'),
        'two_factor_secret' => encrypt('dummysecret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code'])),
        'two_factor_confirmed_at' => now(),
    ]);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect(route('dashboard', absolute: false));
});

it('hides the two-factor settings route when OTP is enabled', function () {
    config()->set('otp.enabled', true);

    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/settings/two-factor');
    $response->assertNotFound();
});
