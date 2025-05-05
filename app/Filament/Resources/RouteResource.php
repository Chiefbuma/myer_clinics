<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RouteResource\Pages;
use App\Models\Route;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RouteResource extends Resource
{
    protected static ?string $model = Route::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationGroupIcon = 'heroicon-o-cog';

    public static function getModelLabel(): string
    {
        return __('Route');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Routes');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::cachedActiveCount();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Route Details')
                    ->schema([
                        Forms\Components\TextInput::make('route_name')
                            ->label('Route Name')
                            ->translateLabel()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->translateLabel()
                            ->required()
                            ->numeric(),

                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->translateLabel()
                            ->required()
                            ->numeric(),

                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Created At')
                            ->translateLabel()
                            ->disabled()
                            ->hiddenOn(['create', 'edit']),

                        Forms\Components\DateTimePicker::make('updated_at')
                            ->label('Updated At')
                            ->translateLabel()
                            ->disabled()
                            ->hiddenOn(['create', 'edit']),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('route_id')
                    ->label('Route ID')
                    ->translateLabel()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('route_name')
                    ->label('Route Name')
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) <= 50 ? null : $state;
                    }),

                Tables\Columns\TextColumn::make('latitude')
                    ->label('Latitude')
                    ->translateLabel()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('longitude')
                    ->label('Longitude')
                    ->translateLabel()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->translateLabel()
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->translateLabel()
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->translateLabel()
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->translateLabel(),
                        Forms\Components\DatePicker::make('created_until')
                            ->default(now())
                            ->translateLabel(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([

                Tables\Actions\Action::make('searchPlace')
                    ->label('Search Place')
                    ->modalHeading('Search Place')
                    ->modalWidth('sm')
                    ->form([
                        Forms\Components\Select::make('place')
                            ->label('Select a location')
                            ->searchable()
                            ->reactive()
                            ->getSearchResultsUsing(fn(string $query) => self::fetchLocationSuggestions($query))
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $selectedPlace = json_decode($state, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $set('latitude', $selectedPlace['lat']);
                                    $set('longitude', $selectedPlace['lon']);
                                }
                            }),

                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->disabled(),

                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->disabled(),

                        Forms\Components\TextInput::make('route_name')
                            ->label('Route Name')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $selectedPlace = json_decode($data['place'], true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            throw new \Exception('Invalid place data');
                        }

                        Route::create([
                            'route_name' => $data['route_name'],
                            'latitude' => $selectedPlace['lat'],
                            'longitude' => $selectedPlace['lon'],
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->form(fn(Form $form) => $form->schema([
                        Forms\Components\TextInput::make('route_name')
                            ->label('Route Name')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('created_at')
                            ->label('Created At')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('updated_at')
                            ->label('Updated At')
                            ->translateLabel()
                            ->disabled(),
                    ]))
                    ->modalWidth('lg')
                    ->slideOver(),

                Tables\Actions\EditAction::make()
                    ->form(fn(Form $form) => $form->schema([
                        Forms\Components\TextInput::make('route_name')
                            ->label('Route Name')
                            ->translateLabel()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->translateLabel()
                            ->required()
                            ->numeric(),

                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->translateLabel()
                            ->required()
                            ->numeric(),
                    ]))
                    ->modalWidth('lg')
                    ->slideOver(),

                Tables\Actions\Action::make('restore')
                    ->label('Restore')
                    ->action(fn(Model $record) => $record->restore())
                    ->visible(fn(Model $record) => $record->trashed())
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Restore Route')
                    ->modalSubheading('Are you sure you want to restore this route?'),

                Tables\Actions\Action::make('forceDelete')
                    ->label('Delete')
                    ->action(fn(Model $record) => $record->forceDelete())
                    ->visible(fn(Model $record) => $record->trashed())
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Permanently Delete Route')
                    ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),

                Tables\Actions\DeleteAction::make()
                    ->label('Deactivate')
                    ->visible(fn(Model $record) => !$record->trashed())
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate Route')
                    ->modalSubheading('The route will be moved to the deactivated list. You can restore it later.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Deactivate')
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Routes')
                        ->modalSubheading('The selected routes will be moved to the deactivated list. You can restore them later.'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Restore')
                        ->requiresConfirmation()
                        ->modalHeading('Restore Routes')
                        ->modalSubheading('Are you sure you want to restore the selected routes?'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Delete')
                        ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Routes')
                        ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistFiltersInSession();
    }

    public static function getRelations(): array
    {
        return [
            // Add relation managers if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoutes::route('/'),
            //'create' => Pages\CreateRoute::route('/create'),
            //'view' => Pages\ViewRoute::route('/{record}'),
            //'edit' => Pages\EditRoute::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes();
    }

    protected static function fetchLocationSuggestions(string $query): array
    {
        if (empty($query)) {
            return [];
        }

        try {
            $client = new Client([
                'headers' => [
                    'User-Agent' => 'MyFilamentApp/1.0 (myemail@example.com)',
                ],
            ]);

            $url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($query);
            $response = $client->get($url);

            if ($response->getStatusCode() !== 200) {
                return [];
            }

            $data = json_decode($response->getBody(), true);
            $options = [];

            foreach ($data as $item) {
                $options[json_encode([
                    'lat' => $item['lat'],
                    'lon' => $item['lon'],
                    'name' => $item['display_name'],
                ])] = $item['display_name'];
            }

            return $options;
        } catch (RequestException $e) {
            return [];
        }
    }
}