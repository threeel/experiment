<?php

declare(strict_types=1);

use App\Jobs\PerformFinalDeletionJob;
use App\Jobs\SendDeletionReminderJob;
use App\Livewire\Settings\DeleteUserForm;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Livewire\Livewire;

it('schedules account deletion and redirects to status page', function () {
    $user = User::factory()->create();

    Bus::fake();

    $this->actingAs($user);

    Livewire::test(DeleteUserForm::class)
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertRedirect(route('account.deletion-status', absolute: false));

    $u = $user->refresh();
    expect($u->delete_scheduled_at)->not()->toBeNull();
    expect($u->delete_token)->not()->toBeNull();

    Bus::assertDispatched(SendDeletionReminderJob::class);
    Bus::assertDispatched(PerformFinalDeletionJob::class);
});

it('redirects any authed route to deletion status when scheduled', function () {
    $user = User::factory()->create([
        'delete_scheduled_at' => now()->addDays(10),
        'delete_token' => Str::random(64),
    ]);

    $this->actingAs($user);

    $this->get('/settings/profile')->assertRedirect(route('account.deletion-status', absolute: false));
});

it('cancels deletion using token route', function () {
    $user = User::factory()->create([
        'delete_scheduled_at' => now()->addDays(10),
        'delete_token' => Str::random(64),
    ]);

    $response = $this->get(route('deletion.cancel', ['token' => $user->delete_token], false));
    $response->assertRedirect(route('login', absolute: false));

    $u = $user->refresh();
    expect($u->delete_scheduled_at)->toBeNull();
    expect($u->delete_token)->toBeNull();
    expect($u->delete_cancelled_at)->not()->toBeNull();
});
