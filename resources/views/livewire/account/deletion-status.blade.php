<div class="flex flex-col items-center gap-6">
    <flux:heading>{{ __('Account Deletion Scheduled') }}</flux:heading>

    <!-- Friendly encouragement message -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    @php($remaining = $this->remaining)
    @if ($remaining !== null)
        <p class="text-center text-zinc-600 dark:text-zinc-400">
            {{ __('Your account is scheduled to be permanently deleted on :date.', ['date' => auth()->user()->delete_scheduled_at->toDayDateTimeString()]) }}
        </p>
        <div wire:poll.1s>
            <flux:badge>
                {{ __('Time remaining: :time', ['time' => now()->diffForHumans(auth()->user()->delete_scheduled_at, ['parts' => 3, 'short' => true, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE])]) }}
            </flux:badge>
        </div>
        <p class="text-center text-sm text-zinc-500 dark:text-zinc-500">
            {{ __('We’d love for you to stay with us! If you changed your mind, you can cancel the deletion below and keep your account.') }}
        </p>

        <div class="mt-2">
            <flux:button variant="primary" wire:click="cancelDeletion">
                {{ __('Keep my account (Cancel deletion)') }}
            </flux:button>
        </div>
    @else
        <p>{{ __('No deletion scheduled.') }}</p>
    @endif
</div>
