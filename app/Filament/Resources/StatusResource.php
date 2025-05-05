<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatusResource\Pages;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StatusResource extends Resource
{
    protected static ?string $model = Status::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationGroupIcon = 'heroicon-o-cog';

    public static function getModelLabel(): string
    {
        return __('Patient Status');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Patient Status');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Status Details')
                    ->schema([
                        Forms\Components\TextInput::make('status')
                            ->label('Status')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Created At')
                            ->disabled()
                            ->hiddenOn(['create', 'edit']),
                        Forms\Components\DateTimePicker::make('updated_at')
                            ->label('Updated At')
                            ->disabled()
                            ->hiddenOn(['create', 'edit']),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status_id')
                    ->label('Status ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until')->default(now()),
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
                        Forms\Components\TextInput::make('status')
                            ->label('Status')
                            ->required()
                            ->maxLength(100),
                    ]))
                    ->modalWidth('lg')
                    ->slideOver(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->form(fn(Form $form) => $form->schema([
                        Forms\Components\TextInput::make('status')
                            ->label('Status')
                            ->disabled(),
                    ]))
                    ->modalWidth('lg')
                    ->slideOver(),

                Tables\Actions\EditAction::make()
                    ->form(fn(Form $form) => $form->schema([
                        Forms\Components\TextInput::make('status')
                            ->label('Status')
                            ->required()
                            ->maxLength(100),
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
                    ->modalHeading('Restore Status')
                    ->modalSubheading('Are you sure you want to restore this status?'),

                Tables\Actions\Action::make('forceDelete')
                    ->label('Delete')
                    ->action(fn(Model $record) => $record->forceDelete())
                    ->visible(fn(Model $record) => $record->trashed())
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Permanently Delete Status')
                    ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),

                Tables\Actions\DeleteAction::make()
                    ->label('Deactivate')
                    ->visible(fn(Model $record) => !$record->trashed())
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate Status')
                    ->modalSubheading('The status will be moved to deactivated list. You can restore it later.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Deactivate')
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Statuses')
                        ->modalSubheading('The selected statuses will be moved to deactivated list. You can restore them later.'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Restore')
                        ->requiresConfirmation()
                        ->modalHeading('Restore Statuses')
                        ->modalSubheading('Are you sure you want to restore the selected statuses?'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Delete')
                        ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Statuses')
                        ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),
                ]),
            ])
            ->defaultSort('status_id', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStatuses::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes();
    }
}