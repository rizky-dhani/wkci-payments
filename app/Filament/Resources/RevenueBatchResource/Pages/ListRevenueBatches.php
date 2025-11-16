<?php

namespace App\Filament\Resources\RevenueBatchResource\Pages;

use App\Filament\Resources\RevenueBatchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRevenueBatches extends ListRecords
{
    protected static string $resource = RevenueBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}