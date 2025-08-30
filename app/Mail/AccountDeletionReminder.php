<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountDeletionReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function build(): self
    {
        return $this->subject(__('Your account is scheduled for deletion'))
            ->view('mail.account-deletion-reminder', [
                'user' => $this->user,
                'cancelUrl' => route('deletion.cancel', ['token' => $this->user->delete_token], absolute: false),
                'scheduledAt' => $this->user->delete_scheduled_at,
            ]);
    }
}
