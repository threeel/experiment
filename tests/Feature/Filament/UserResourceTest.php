<?php

declare(strict_types=1);

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function adminUser(): User
{
    return User::factory()->create([
        'email' => 'lefteris.k@3elalliance.com',
    ]);
}

it('lists users in filament', function () {
    $admin = adminUser();
    $users = User::factory()->count(3)->create();

    $this->actingAs($admin);

    Filament::setCurrentPanel('admin');

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords($users);
});

it('creates a user via filament resource', function () {
    $admin = adminUser();

    $this->actingAs($admin);

    Filament::setCurrentPanel('admin');

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'secret123',
            'passwordConfirmation' => 'secret123',
        ])
        ->call('create')
        ->assertNotified()
        ->assertHasNoFormErrors();

    expect(User::whereEmail('test@example.com')->exists())->toBeTrue();
});
