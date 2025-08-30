<?php

declare(strict_types=1);

namespace App\Livewire\Account;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class DeletionStatus extends Component
{
    public function getRemainingProperty(): ?int
    {
        $user = Auth::user();
        if (! $user || ! $user->delete_scheduled_at) {
            return null;
        }
        $seconds = now()->diffInSeconds($user->delete_scheduled_at, false);

        return (int) max(0, (int) $seconds);
    }

    public function cancelDeletion(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->delete_scheduled_at) {
            // Nothing to cancel
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        $user->forceFill([
            'delete_scheduled_at' => null,
            'delete_cancelled_at' => now(),
            'delete_token' => null,
        ])->save();

        session()->flash('status', __("We're glad you're staying! Your account deletion has been cancelled."));

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}
