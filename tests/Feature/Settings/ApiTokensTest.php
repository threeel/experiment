<?php

declare(strict_types=1);

use App\Livewire\Settings\Api as ApiComponent;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

it('shows the api settings page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/settings/api')
        ->assertOk()
        ->assertSeeLivewire(ApiComponent::class);
});

it('can create a personal access token and see copy button', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ApiComponent::class)
        ->set('tokenName', 'Test Token')
        ->call('createToken')
        ->assertHasNoErrors()
        ->assertSet('tokenName', '')
        ->assertSet('plainTextToken', fn ($value) => is_string($value) && Str::of($value)->contains('|'))
        ->assertSeeHtml('data-testid="copy-token-button"');

    expect($user->tokens()->count())->toBe(1);
});

it('can revoke a token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('To Revoke');

    Livewire::actingAs($user)
        ->test(ApiComponent::class)
        ->call('revokeToken', $token->accessToken->id ?? $user->tokens()->first()->id)
        ->assertHasNoErrors();

    expect($user->tokens()->count())->toBe(0);
});
