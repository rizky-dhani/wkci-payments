<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RevenueBatchResource\Pages;
use App\Models\RevenueBatch;
use App\Models\TransactionHistory;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RevenueBatchResource extends Resource
{
    protected static ?string $model = RevenueBatch::class;

    protected static ?string $recordTitleAttribute = 'revenue_batch_id';

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                RevenueBatch::unique()
            )
            ->columns([
                Tables\Columns\TextColumn::make('revenue_batch_id')
                    ->label('Revenue Batch ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_count')
                    ->label('Transaction Count')
                    ->sortable(),
                Tables\Columns\TextColumn::make('latest_transaction_date')
                    ->label('Latest Transaction Date')
                    ->date()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // You can add bulk actions here if needed
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRevenueBatches::route('/'),
            'view' => Pages\ViewRevenueBatch::route('/{record}'),
        ];
    }
}