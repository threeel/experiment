<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('updatePassword')
                ->label('Update Password')
                ->form([
                    TextInput::make('password')
                        ->label('New Password')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(8),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'password' => $data['password'],
                    ]);

                    Notification::make()
                        ->title('Password updated')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
