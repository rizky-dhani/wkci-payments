<?php

namespace App\Filament\Resources\ApiRequestResource\Pages;

use App\Filament\Resources\ApiRequestResource;
use App\Models\ApiRequest;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewApiRequest extends ViewRecord
{
    protected static string $resource = ApiRequestResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Request Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('endpoint')
                            ->label('Endpoint')
                            ->columnSpanFull(),

                        TextEntry::make('method')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'GET' => 'success',
                                'POST' => 'warning',
                                'PUT' => 'info',
                                'DELETE' => 'danger',
                                'PATCH' => 'primary',
                                default => 'gray',
                            }),

                        TextEntry::make('response_status')
                            ->badge()
                            ->color(fn (int $state): string => match (true) {
                                $state < 300 => 'success',
                                $state < 400 => 'warning',
                                $state < 500 => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('execution_time')
                            ->label('Execution Time (ms)'),

                        TextEntry::make('created_at')
                            ->dateTime(),
                    ]),

                Section::make('Request Headers')
                    ->schema([
                        TextEntry::make('request_headers')
                            ->columnSpanFull()
                            ->copyable()
                            ->copyMessage('Headers copied')
                            ->extraAttributes(['class' => 'font-mono text-sm']),
                    ])
                    ->collapsible(),

                Section::make('Request Body')
                    ->schema([
                        TextEntry::make('request_body')
                            ->columnSpanFull()
                            ->copyable()
                            ->copyMessage('Body copied')
                            ->extraAttributes(['class' => 'font-mono text-sm']),
                    ])
                    ->collapsible(),

                Section::make('Response Headers')
                    ->schema([
                        TextEntry::make('response_headers')
                            ->columnSpanFull()
                            ->copyable()
                            ->copyMessage('Headers copied')
                            ->extraAttributes(['class' => 'font-mono text-sm']),
                    ])
                    ->collapsible(),

                Section::make('Response Body')
                    ->schema([
                        TextEntry::make('response_body')
                            ->columnSpanFull()
                            ->copyable()
                            ->copyMessage('Body copied')
                            ->extraAttributes(['class' => 'font-mono text-sm']),
                    ])
                    ->collapsible(),

                Section::make('Error Information')
                    ->visible(fn ($record) => ! empty($record->error_message))
                    ->schema([
                        TextEntry::make('error_message')
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'text-danger-600']),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('retry')
                ->action(fn (ApiRequest $record) => $this->retryRequest($record))
                ->requiresConfirmation()
                ->visible(fn (ApiRequest $record) => $record->response_status >= 400)
                ->color('warning')
                ->icon('heroicon-o-arrow-path'),

            Actions\EditAction::make()
                ->visible(false), // Hide edit action for this read-only resource

            Actions\DeleteAction::make(),
        ];
    }

    private function retryRequest(ApiRequest $record): void
    {
        // Logic to retry the API request would go here
        // This is just a placeholder implementation
        $client = new \GuzzleHttp\Client;

        try {
            $response = $client->request(
                $record->method,
                $record->endpoint,
                [
                    'headers' => $record->request_headers ?? [],
                    'body' => $record->request_body ? json_encode($record->request_body) : null,
                ]
            );

            // Update the record with the new response
            $record->update([
                'response_status' => $response->getStatusCode(),
                'response_headers' => $response->getHeaders(),
                'response_body' => json_decode($response->getBody()->getContents(), true),
                'error_message' => null,
            ]);

            $this->notify('success', 'Request retried successfully');
        } catch (\Exception $e) {
            $record->update([
                'error_message' => $e->getMessage(),
            ]);

            $this->notify('error', 'Request retry failed: '.$e->getMessage());
        }
    }
}
