<?php

namespace App\Filament\Resources;

use App\Filament\Actions\ImportTransactionHistoryAction;
use App\Filament\Resources\TransactionHistoryResource\Pages;
use App\Models\TransactionHistory;
use App\Services\PaymentApiService;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class TransactionHistoryResource extends Resource
{
    protected static ?string $model = TransactionHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationBadge(): ?string
    {
        // Optionally show a count of sent transactions in the navigation
        return TransactionHistory::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Transaction History')
                    ->columns(3)
                    ->schema([
                        Forms\Components\DatePicker::make('transaction_date')
                            ->label('Transaction Date')
                            ->placeholder('DD/MM/YYYY')
                            ->native(false)
                            ->default(fn () => now())
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\TimePicker::make('transaction_time')
                            ->label('Transaction Time')
                            ->placeholder('HH:MM')
                            ->native(false)
                            ->seconds(false)
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->columnSpanFull()
                            ->required()
                            ->numeric()
                            ->prefix('IDR'),
                        Forms\Components\Textarea::make('remarks')
                            ->label('Remarks')
                            ->columnSpanFull()
                            ->maxLength(1000),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('Transaction Number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Transaction Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('transaction_time')
                    ->label('Time')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR', 0, 'id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remarks')
                    ->label('Remarks')
                    ->searchable()
                    ->limit(50), // Limiting length for better table display

                Tables\Columns\TextColumn::make('revenue_batch_id')
                    ->label('Revenue Batch ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('upload_to_api')
                    ->label('Upload to Lippo API')
                    ->action(function (TransactionHistory $record) {
                        $service = new PaymentApiService;

                        $transactionData = [
                            [
                                'TransactionNumber' => $record->transaction_number,
                                'TransactionDate' => $record->transaction_date->format('Y-m-d'), // Format as date string (YYYY-MM-DD)
                                'Amount' => floatval($record->amount), // Ensure amount is sent as a numeric value
                                'Remarks' => substr($record->remarks ?? '', 0, 50), // Truncate remarks to 50 chars
                            ],
                        ];

                        $result = $service->saveRevenue($transactionData);

                        if ($result['success']) {
                            $response = $result['data'];
                            $message = "Successfully uploaded transaction {$record->transaction_number} to Lippo API";

                            // Store the RevenueBatchId if it's present in the response
                            if (isset($response['RevenueBatchId'])) {
                                $record->update(['revenue_batch_id' => $response['RevenueBatchId']]);
                                $message .= " with Revenue Batch ID: {$response['RevenueBatchId']}";
                            }

                            if (isset($response['ErrorTransactionNumber']) && ! empty($response['ErrorTransactionNumber'])) {
                                $message .= '. Error: '.implode(', ', $response['ErrorTransactionNumber']);
                                Notification::make()
                                    ->title($message)
                                    ->warning()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title($message)
                                    ->success()
                                    ->send();
                            }
                        } else {
                            Notification::make()
                                ->title('Failed to upload transaction')
                                ->body($result['error'] ?? 'Unknown error')
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->color('success')
                    ->icon('heroicon-o-arrow-up-tray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Upload selected transactions to Lippo API
                    BulkAction::make('upload_selected')
                        ->label('Upload Selected to Lippo API')
                        ->action(function (Collection $selectedRecords) {
                            $service = new PaymentApiService;

                            $transactionData = [];
                            foreach ($selectedRecords as $record) {
                                $transactionData[] = [
                                    'TransactionNumber' => $record->transaction_number,
                                    'TransactionDate' => $record->transaction_date->format('Y-m-d'),
                                    'Amount' => $record->amount,
                                    'Remarks' => $record->remarks,
                                ];
                            }

                            $result = $service->saveRevenue($transactionData);

                            if ($result['success']) {
                                $response = $result['data'];
                                $message = "Successfully uploaded {$selectedRecords->count()} transactions to Lippo API";

                                // Store the RevenueBatchId if it's present in the response
                                if (isset($response['RevenueBatchId'])) {
                                    // Update all selected records with the same RevenueBatchId
                                    $selectedRecords->each(function ($record) use ($response) {
                                        $record->update(['revenue_batch_id' => $response['RevenueBatchId']]);
                                    });
                                    $message .= " with Revenue Batch ID: {$response['RevenueBatchId']}";
                                }

                                if (isset($response['ErrorTransactionNumber']) && ! empty($response['ErrorTransactionNumber'])) {
                                    $message .= '. Some transactions had errors: '.implode(', ', $response['ErrorTransactionNumber']);
                                }

                                $this->notify('success', $message);
                            } else {
                                $this->notify('error', 'Failed to upload transactions: '.($result['error'] ?? 'Unknown error'));
                            }
                        })
                        ->color('success')
                        ->icon('heroicon-o-arrow-up-tray'),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTransactionHistories::route('/'),
        ];
    }
}
