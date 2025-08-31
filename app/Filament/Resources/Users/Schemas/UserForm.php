<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    /**
     * @throws \Exception
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(6)
            ->components([

                Section::make([
                    TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Email address')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->scopedUnique()->disabled(),
                ])->grow()->columnSpan(4),

                Section::make([
                    Toggle::make('email_verified_at')
                        ->label('Email verified')
                        ->disabled()
                        ->helperText(fn ($record) => $record && $record->email_verified_at ? 'Verified on '.$record->email_verified_at->toDayDateTimeString() : 'Not verified'),
                    Toggle::make('delete_token')
                        ->label('Marked For Deletion')
                        ->disabled()
                        ->helperText(fn ($record) => $record && $record->delete_scheduled_at ? 'Scheduled for deletion on '.$record->delete_scheduled_at->toDayDateTimeString() : 'Not currently marked for deletion'),

                ])->heading('User Settings')
                    ->grow(false)
                    ->columnSpan(2),

            ]);
    }
}
