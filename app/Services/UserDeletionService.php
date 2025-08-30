<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserDeletionService
{
    public function deleteUserAndData(User $user): void
    {
        DB::transaction(function () use ($user): void {
            // Delete API tokens if Sanctum/Passport is installed
            if (method_exists($user, 'tokens') && Schema::hasTable('personal_access_tokens')) {
                $user->tokens()->delete();
            }

            // Delete database notifications if the table exists
            if (method_exists($user, 'notifications') && Schema::hasTable('notifications')) {
                $user->notifications()->delete();
            }

            // TODO: Delete other related data here as your app grows.

            // Finally delete user
            $user->delete();
        });
    }
}
