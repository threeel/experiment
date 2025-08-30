<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Api extends Component
{
    public string $tokenName = '';

    /**
     * The plain text token to show once after creation.
     */
    public ?string $plainTextToken = null;

    public function createToken(): void
    {
        $this->validate([
            'tokenName' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        // Create token with default "*" ability. You may refine abilities later.
        $token = $user->createToken($this->tokenName, ['*']);

        $this->plainTextToken = $token->plainTextToken;
        $this->tokenName = '';

        $this->dispatch('token-created');
    }

    public function revokeToken(int $tokenId): void
    {
        $user = Auth::user();

        $user->tokens()->whereKey($tokenId)->delete();

        $this->dispatch('token-revoked');
    }
}
