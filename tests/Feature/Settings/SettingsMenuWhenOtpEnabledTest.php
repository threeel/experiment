<?php

declare(strict_types=1);

use App\Models\User;

it('renders settings profile without two-factor link when OTP is enabled', function () {
    config()->set('otp.enabled', true);

    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->get('/settings/profile');

    $response->assertSuccessful();
    $response->assertDontSee('Two-Factor Auth');
});
