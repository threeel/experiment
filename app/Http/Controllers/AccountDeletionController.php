<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;

class AccountDeletionController
{
    public function cancel(string $token): RedirectResponse
    {
        /** @var User|null $user */
        $user = User::query()->where('delete_token', $token)->first();
        if (! $user || ! $user->delete_scheduled_at) {
            return redirect()->route('dashboard');
        }

        $user->forceFill([
            'delete_scheduled_at' => null,
            'delete_cancelled_at' => now(),
            'delete_token' => null,
        ])->save();

        return redirect()->route('login')->with('status', __('Your account deletion has been cancelled. You may log in again.'));
    }
}
