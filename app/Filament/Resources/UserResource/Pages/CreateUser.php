<?php

namespace App\Filament\Resources\UserResource\Pages;

use Hash;
use Filament\Actions;
use Illuminate\Support\Str;
use App\Filament\Resources\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['userId'] = Str::orderedUuid();
        $data['password'] = Hash::make('Wkci2025!');
        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('User registered')
            ->body('User has been created successfully!');
    }
}
