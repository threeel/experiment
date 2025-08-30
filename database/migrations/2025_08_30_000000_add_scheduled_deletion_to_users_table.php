<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('delete_scheduled_at')->nullable()->after('email_verified_at');
            $table->timestamp('delete_cancelled_at')->nullable()->after('delete_scheduled_at');
            $table->string('delete_token', 64)->nullable()->unique()->after('delete_cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['delete_token']);
            $table->dropColumn(['delete_scheduled_at', 'delete_cancelled_at', 'delete_token']);
        });
    }
};
