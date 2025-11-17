<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApiRequestResource\Pages;
use App\Filament\Resources\ApiRequestResource\Pages\ListApiRequests;
use App\Models\ApiRequest;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApiRequestResource extends Resource
{
    protected static ?string $model = ApiRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $label = 'API Request';

    protected static ?string $pluralLabel = 'API Requests';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Form is not needed for this read-only resource
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('endpoint')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('method')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'GET' => 'success',
                        'POST' => 'warning',
                        'PUT' => 'info',
                        'DELETE' => 'danger',
                        'PATCH' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('response_status')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state < 300 => 'success',
                        $state < 400 => 'warning',
                        $state < 500 => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('method')
                    ->options([
                        'GET' => 'GET',
                        'POST' => 'POST',
                        'PUT' => 'PUT',
                        'DELETE' => 'DELETE',
                        'PATCH' => 'PATCH',
                    ]),

                Tables\Filters\SelectFilter::make('response_status')
                    ->options([
                        '200' => '200 - OK',
                        '201' => '201 - Created',
                        '400' => '400 - Bad Request',
                        '401' => '401 - Unauthorized',
                        '403' => '403 - Forbidden',
                        '404' => '404 - Not Found',
                        '500' => '500 - Server Error',
                    ]),

                Tables\Filters\Filter::make('successful_requests')
                    ->label('Successful Requests')
                    ->query(fn (Builder $query) => $query->successful()),

                Tables\Filters\Filter::make('failed_requests')
                    ->label('Failed Requests')
                    ->query(fn (Builder $query) => $query->failed()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('retry')
                    ->action(fn (ApiRequest $record) => static::retryRequest($record))
                    ->requiresConfirmation()
                    ->visible(fn (ApiRequest $record) => $record->response_status >= 400),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Request Information')
                    ->columns(2)
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('endpoint')
                            ->label('Endpoint')
                            ->columnSpanFull(),

                        \Filament\Infolists\Components\TextEntry::make('method')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'GET' => 'success',
                                'POST' => 'warning',
                                'PUT' => 'info',
                                'DELETE' => 'danger',
                                'PATCH' => 'primary',
                                default => 'gray',
                            }),

                        \Filament\Infolists\Components\TextEntry::make('response_status')
                            ->badge()
                            ->color(fn (int $state): string => match (true) {
                                $state < 300 => 'success',
                                $state < 400 => 'warning',
                                $state < 500 => 'danger',
                                default => 'gray',
                            }),

                        \Filament\Infolists\Components\TextEntry::make('execution_time')
                            ->label('Execution Time (ms)'),

                        \Filament\Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                    ]),

                \Filament\Infolists\Components\Section::make('Request Headers')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('request_headers')
                            ->columnSpanFull()
                            ->copyable()
                            ->copyMessage('Headers copied')
                            ->extraAttributes(['class' => 'font-mono text-sm']),
                    ])
                    ->collapsible(),

                \Filament\Infolists\Components\Section::make('Request Body')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('request_body')
                            ->columnSpanFull()
                            ->copyable()
                            ->copyMessage('Body copied')
                            ->extraAttributes(['class' => 'font-mono text-sm']),
                    ])
                    ->collapsible(),

                \Filament\Infolists\Components\Section::make('Response Headers')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('response_headers')
                            ->columnSpanFull()
                            ->copyable()
                            ->copyMessage('Headers copied')
                            ->extraAttributes(['class' => 'font-mono text-sm']),
                    ])
                    ->collapsible(),

                \Filament\Infolists\Components\Section::make('Response Body')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('response_body')
                            ->columnSpanFull()
                            ->copyable()
                            ->copyMessage('Body copied')
                            ->extraAttributes(['class' => 'font-mono text-sm']),
                    ])
                    ->collapsible(),

                \Filament\Infolists\Components\Section::make('Error Information')
                    ->visible(fn ($record) => ! empty($record->error_message))
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('error_message')
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'text-danger-600']),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApiRequests::route('/'),
            'view' => Pages\ViewApiRequest::route('/{record}'),
        ];
    }

    private static function retryRequest(ApiRequest $record): void
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
        } catch (\Exception $e) {
            $record->update([
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
