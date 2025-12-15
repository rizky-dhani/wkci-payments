<?php

namespace App\Filament\Actions;

use App\Imports\TransactionHistoryImport;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;

class ImportTransactionHistoryAction
{
    public static function make(): Action
    {
        return Action::make('import_excel')
            ->label('Import from Excel')
            ->button()
            ->form([
                \Filament\Forms\Components\FileUpload::make('excel_file')
                    ->label('Excel File')
                    ->required()
                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv'])
                    ->maxSize(10240) // 10MB
                    ->disk('local') // You can change this to your preferred disk
                    ->directory('imports') // Store in imports directory
                    ->visibility('private'),
            ])
            ->action(function (array $data) {
                try {
                    $filePath = $data['excel_file'];

                    // Import the data
                    Excel::import(new TransactionHistoryImport, $filePath);

                    Notification::make()
                        ->title('Import successful')
                        ->body('Transaction history data has been imported successfully.')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    \Log::error('TransactionHistory import failed: ' . $e->getMessage());

                    Notification::make()
                        ->title('Import failed')
                        ->body('Error: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}