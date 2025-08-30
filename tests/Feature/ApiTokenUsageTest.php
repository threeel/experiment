<?php

declare(strict_types=1);

use App\Models\User;

it('can create a token and access a protected api route', function () {
    $user = User::factory()->create();

    $plain = $user->createToken('CLI Test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$plain)
        ->getJson('/api/user');

    $response->assertSuccessful()
        ->assertJsonFragment(['id' => $user->id])
        ->assertJsonFragment(['email' => $user->email]);
});

it('cannot access protected api without a valid token', function () {
    $this->getJson('/api/user')->assertUnauthorized();

    $user = User::factory()->create();
    // Malformed/invalid token
    $this->withHeader('Authorization', 'Bearer invalid-token')
        ->getJson('/api/user')
        ->assertUnauthorized();
});
