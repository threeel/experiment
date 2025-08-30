<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\UserDeletionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PerformFinalDeletionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $userId) {}

    public function handle(UserDeletionService $service): void
    {
        $user = User::find($this->userId);
        if (! $user || ! $user->delete_scheduled_at || $user->delete_cancelled_at) {
            return;
        }

        $service->deleteUserAndData($user);
    }
}
