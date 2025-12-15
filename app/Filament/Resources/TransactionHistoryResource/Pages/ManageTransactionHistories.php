<?php

namespace App\Filament\Resources\TransactionHistoryResource\Pages;

use App\Filament\Actions\ImportTransactionHistoryAction;
use Filament\Actions;
use App\Models\TransactionHistory;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use App\Services\PaymentApiService;
use Filament\Support\Enums\MaxWidth;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconPosition;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\TransactionHistoryResource;

class ManageTransactionHistories extends ManageRecords
{
    protected static string $resource = TransactionHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportTransactionHistoryAction::make(),
            // Actions\Action::make('revenue_batches')
            //     ->label('Revenue Batches')
            //     ->url(fn () => \App\Filament\Resources\RevenueBatchResource::getUrl('index'))
            //     ->color('info'),
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
