<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeleteUserForm extends Component
{
    public string $password = '';

    /**
     * Schedule deletion for the currently authenticated user.
     */
    public function deleteUser(): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $user = Auth::user();
        if ($user->delete_scheduled_at) {
            return;
        }

        // Reuse Profile scheduling via event dispatch to keep single logic? Implement inline minimal.
        $graceDays = (int) config('account_deletion.grace_days', 30);
        $reminderDays = (int) config('account_deletion.reminder_days_before', 3);

        $user->forceFill([
            'delete_scheduled_at' => now()->addDays($graceDays),
            'delete_cancelled_at' => null,
            'delete_token' => \Illuminate\Support\Str::random(64),
        ])->save();

        // Queue jobs
        $reminderAt = $user->delete_scheduled_at->copy()->subDays($reminderDays);
        if ($reminderAt->isFuture()) {
            \App\Jobs\SendDeletionReminderJob::dispatch($user->id)->delay($reminderAt);
        }
        \App\Jobs\PerformFinalDeletionJob::dispatch($user->id)->delay($user->delete_scheduled_at);

        // Redirect user to the deletion status page
        $this->redirect(route('account.deletion-status', absolute: false), navigate: true);
    }
}
