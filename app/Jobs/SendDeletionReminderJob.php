<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\AccountDeletionReminder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDeletionReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $userId) {}

    public function handle(): void
    {
        $user = User::find($this->userId);
        if (! $user || ! $user->delete_scheduled_at || $user->delete_cancelled_at) {
            return;
        }

        Mail::to($user->email)->send(new AccountDeletionReminder($user));
    }
}
