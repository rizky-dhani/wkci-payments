<?php

namespace App\Filament\Resources\RevenueBatchResource\Pages;

use App\Filament\Resources\RevenueBatchResource;
use App\Models\TransactionHistory;
use Filament\Actions;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewRevenueBatch extends ViewRecord
{
    protected static string $resource = RevenueBatchResource::class;

    protected static ?string $title = 'View Revenue Batch';

    public function getTitle(): string
    {
        return 'View Revenue Batch #' . $this->getRecord()?->revenue_batch_id;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Transaction List')
                    ->schema([
                        RepeatableEntry::make('transactionHistories')
                            ->schema([
                                TextEntry::make('transaction_number')
                                    ->label('Transaction Number'),
                                    TextEntry::make('remarks')
                                        ->label('Remarks')
                                        ->limit(50),
                                TextEntry::make('transaction_time')
                                    ->label('Transaction Time'),
                                TextEntry::make('amount')
                                    ->label('Amount')
                                    ->money('IDR', 0, 'id'),
                            ])
                            ->columns(4)
                    ]),
            ]);
    }
}