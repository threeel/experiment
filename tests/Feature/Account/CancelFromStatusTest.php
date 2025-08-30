<?php

declare(strict_types=1);

use App\Livewire\Account\DeletionStatus;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('allows cancelling deletion from deletion status page', function () {
    $user = User::factory()->create([
        'delete_scheduled_at' => now()->addDays(10),
        'delete_token' => Str::random(64),
    ]);

    actingAs($user);

    Livewire::test(DeletionStatus::class)
        ->call('cancelDeletion')
        ->assertRedirect(route('dashboard', absolute: false));

    $fresh = $user->refresh();
    expect($fresh->delete_scheduled_at)->toBeNull();
    expect($fresh->delete_token)->toBeNull();
    expect($fresh->delete_cancelled_at)->not()->toBeNull();

    // After cancellation, user should be able to access settings normally
    $this->get('/settings/profile')->assertOk();
});
