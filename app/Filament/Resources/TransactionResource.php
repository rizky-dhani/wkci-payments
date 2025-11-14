<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('trx_order_no')
                    ->label('Order #')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('partner_ref_no')
                    ->label('Partner Ref. Number')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('customer_name')
                    ->label('Customer Name')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('payment_status')
                    ->label('Payment Status')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('paid_at')
                    ->label('Paid At'),
                Forms\Components\TextInput::make('amount')
                    ->label('Amount')
                    ->required()
                    ->maxLength(255)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query->orderByDesc('trx_order_no');
            })
            ->columns([
                Tables\Columns\TextColumn::make('trx_order_no')
                    ->label('Order #')
                    ->searchable(),
                Tables\Columns\TextColumn::make('partner_ref_no')
                    ->label('Partner Reference No.')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label(label: 'Customer Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->searchable(),
                Tables\Columns\TextColumn::make('submitted_date')
                    ->label('Submitted At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
