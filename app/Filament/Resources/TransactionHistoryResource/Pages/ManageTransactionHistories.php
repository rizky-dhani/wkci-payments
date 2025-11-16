<?php

namespace App\Filament\Resources\TransactionHistoryResource\Pages;

use App\Filament\Resources\TransactionHistoryResource;
use App\Models\TransactionHistory;
use App\Services\PaymentApiService;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\MaxWidth;

class ManageTransactionHistories extends ManageRecords
{
    protected static string $resource = TransactionHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('revenue_batches')
                ->label('Revenue Batches')
                ->url(fn () => \App\Filament\Resources\RevenueBatchResource::getUrl('index'))
                ->color('info'),
            CreateAction::make()
                ->color('success')
                ->successNotificationTitle('Transaction History created successfully')
                ->icon('heroicon-o-plus')
                ->iconPosition(IconPosition::Before)
                ->modalWidth(MaxWidth::SevenExtraLarge),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Add any custom widgets if needed
        ];
    }
}
