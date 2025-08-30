<?php

declare(strict_types=1);

use App\Models\User;

it('renders two-factor challenge page using guest layout', function () {
    // Fortify's route is guest, so ensure no auth session
    auth()->logout();

    $user = User::factory()->create();

    // Simulate the pre-challenge login state Fortify expects
    $this->withSession(['login.id' => $user->id]);

    $response = $this->get(route('two-factor.login'));

    $response->assertSuccessful()->assertSee('Two-Factor Authentication');
});
