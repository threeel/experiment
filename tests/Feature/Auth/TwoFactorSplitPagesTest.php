<?php

declare(strict_types=1);

use App\Models\User;

it('renders the OTP challenge page without recovery input and has link to recovery page', function () {
    auth()->logout();

    $user = User::factory()->create();
    $this->withSession(['login.id' => $user->id]);

    $response = $this->get(route('two-factor.login'));

    $response->assertSuccessful()
        ->assertSee('Two-Factor Authentication')
        ->assertSee('Use Recovery Code instead')
        ->assertDontSee('name="recovery_code"');
});

it('renders the recovery challenge page and links back to OTP page', function () {
    auth()->logout();

    $user = User::factory()->create();
    $this->withSession(['login.id' => $user->id]);

    $response = $this->get(route('two-factor.login.recovery'));

    $response->assertSuccessful()
        ->assertSee('Recovery Code')
        ->assertSee('Use Authentication Code instead');
});
