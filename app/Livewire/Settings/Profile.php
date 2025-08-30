<?php

namespace App\Livewire\Settings;

use App\Jobs\PerformFinalDeletionJob;
use App\Jobs\SendDeletionReminderJob;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Profile extends Component
{
    public string $name = '';

    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    public function scheduleDeletion(): void
    {
        $user = Auth::user();

        if ($user->delete_scheduled_at) {
            return;
        }

        $graceDays = (int) config('account_deletion.grace_days', 30);
        $reminderDays = (int) config('account_deletion.reminder_days_before', 3);

        $user->forceFill([
            'delete_scheduled_at' => now()->addDays($graceDays),
            'delete_cancelled_at' => null,
            'delete_token' => Str::random(64),
        ])->save();

        // Queue jobs
        $reminderAt = $user->delete_scheduled_at->copy()->subDays($reminderDays);
        if ($reminderAt->isFuture()) {
            SendDeletionReminderJob::dispatch($user->id)->delay($reminderAt);
        }
        PerformFinalDeletionJob::dispatch($user->id)->delay($user->delete_scheduled_at);

        $this->dispatch('deletion-scheduled');
    }
}
