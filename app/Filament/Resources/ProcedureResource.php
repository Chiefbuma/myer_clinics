<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcedureResource\Pages;
use App\Models\Procedure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProcedureResource extends Resource
{
    protected static ?string $model = Procedure::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationGroupIcon = 'heroicon-o-cog';

    public static function getModelLabel(): string
    {
        return __('Procedure');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Procedures');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::cachedActiveCount();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Procedure')
                    ->schema([
                        Forms\Components\TextInput::make('procedure_name')
                            ->label('Procedure Name')
                            ->translateLabel()
                            ->required()
                            ->maxLength(255),

                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('procedure_id')
                    ->label('Procedure ID')
                    ->translateLabel()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('procedure_name')
                    ->label('Procedure Name')
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) <= 50 ? null : $state;
                    }),

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
                Tables\Actions\CreateAction::make()
                    ->form(fn(Form $form) => $form->schema([
                        Forms\Components\TextInput::make('procedure_name')
                            ->label('Procedure Name')
                            ->translateLabel()
                            ->required()
                            ->maxLength(255),

                    ]))
                    ->modalWidth('lg')
                    ->slideOver(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->form(fn(Form $form) => $form->schema([
                        Forms\Components\TextInput::make('procedure_name')
                            ->label('Procedure Name')
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
                        Forms\Components\TextInput::make('procedure_name')
                            ->label('Procedure Name')
                            ->translateLabel()
                            ->required()
                            ->maxLength(255),


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
                    ->modalHeading('Restore Procedure')
                    ->modalSubheading('Are you sure you want to restore this procedure?'),

                Tables\Actions\Action::make('forceDelete')
                    ->label('Delete')
                    ->action(fn(Model $record) => $record->forceDelete())
                    ->visible(fn(Model $record) => $record->trashed())
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Permanently Delete Procedure')
                    ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),

                Tables\Actions\DeleteAction::make()
                    ->label('Deactivate')
                    ->visible(fn(Model $record) => !$record->trashed())
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate Procedure')
                    ->modalSubheading('The procedure will be moved to the deactivated list. You can restore it later.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Deactivate')
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Procedures')
                        ->modalSubheading('The selected procedures will be moved to the deactivated list. You can restore them later.'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Restore')
                        ->requiresConfirmation()
                        ->modalHeading('Restore Procedures')
                        ->modalSubheading('Are you sure you want to restore the selected procedures?'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Delete')
                        ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Procedures')
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
            'index' => Pages\ListProcedures::route('/'),
            //'create' => Pages\CreateProcedure::route('/create'),
            //'view' => Pages\ViewProcedure::route('/{record}'),
            //'edit' => Pages\EditProcedure::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes();
    }
}