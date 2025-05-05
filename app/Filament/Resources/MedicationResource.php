<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicationResource\Pages;
use App\Models\Medication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;
use Maatwebsite\Excel\Excel;

class MedicationResource extends Resource
{
    protected static ?string $model = Medication::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $modelLabel = 'Medication';
    protected static ?string $navigationLabel = 'Medications';

    public static function getModelLabel(): string
    {
        return __('Medication');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Medications');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::cachedActiveCount();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Medication Details')
                    ->schema([
                        Forms\Components\TextInput::make('item_name')
                            ->label('Medication Name')
                            ->required()
                            ->maxLength(255)
                            ->inlineLabel(),
                        Forms\Components\Textarea::make('composition')
                            ->label('Composition')
                            ->maxLength(65535)
                            ->inlineLabel(),
                        Forms\Components\Select::make('brand')
                            ->label('Brand')
                            ->options([
                                'brand' => 'Brand',
                                'generic' => 'Generic',
                                'original' => 'Original',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('formulation')
                            ->label('Formulation')
                            ->options([
                                'tablet' => 'Tablet',
                                'injection' => 'Injection',
                                'capsule' => 'Capsule',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('category')
                            ->label('Category')
                            ->options([
                                'post-transplant_medications' => 'Post-Transplant Medications',
                                'diabetes_care' => 'Diabetes Care',
                                'oncology_drugs' => 'Oncology Drugs',
                                'cardiovascular' => 'Cardiovascular',
                                'mens_health' => 'Men\'s Health',
                                'renal' => 'Renal',
                            ])
                            ->required()
                            ->inlineLabel(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Timestamps')
                    ->schema([
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Created At')
                            ->translateLabel()
                            ->disabled()
                            ->hiddenOn(['create', 'edit'])
                            ->inlineLabel(),
                        Forms\Components\DateTimePicker::make('updated_at')
                            ->label('Updated At')
                            ->translateLabel()
                            ->disabled()
                            ->hiddenOn(['create', 'edit'])
                            ->inlineLabel(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_name')
                    ->label('Medication Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('composition')
                    ->label('Composition')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn($state) => $state),
                Tables\Columns\TextColumn::make('brand')
                    ->label('Brand')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('formulation')
                    ->label('Formulation')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->formatStateUsing(fn($state) => str_replace('_', ' ', ucwords($state)))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'post-transplant_medications' => 'Post-Transplant Medications',
                        'diabetes_care' => 'Diabetes Care',
                        'oncology_drugs' => 'Oncology Drugs',
                        'cardiovascular' => 'Cardiovascular',
                        'mens_health' => 'Men\'s Health',
                        'renal' => 'Renal',
                    ])
                    ->label('Category'),
                Tables\Filters\SelectFilter::make('brand')
                    ->options([
                        'brand' => 'Brand',
                        'generic' => 'Generic',
                        'original' => 'Original',
                    ])
                    ->label('Brand'),
                Tables\Filters\SelectFilter::make('formulation')
                    ->options([
                        'tablet' => 'Tablet',
                        'injection' => 'Injection',
                        'capsule' => 'Capsule',
                    ])
                    ->label('Formulation'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalWidth('3xl')
                    ->slideOver(),
                ExportAction::make()
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        ExcelExport::make()
                            ->withFilename('medication_export_' . now()->format('Y-m-d_H-i-s'))
                            ->withColumns([
                                Column::make('item_name')
                                    ->heading('Medication Name'),
                                Column::make('composition')
                                    ->heading('Composition'),
                                Column::make('brand')
                                    ->heading('Brand')
                                    ->formatStateUsing(fn($state) => ucfirst($state)),
                                Column::make('formulation')
                                    ->heading('Formulation')
                                    ->formatStateUsing(fn($state) => ucfirst($state)),
                                Column::make('category')
                                    ->heading('Category')
                                    ->formatStateUsing(fn($state) => str_replace('_', ' ', ucwords($state))),
                                Column::make('created_at')
                                    ->heading('Created At')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i') : 'N/A'),
                            ])
                            ->withWriterType(Excel::XLSX)
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->form(fn(Form $form) => $form->schema([
                        Forms\Components\TextInput::make('item_name')
                            ->label('Medication Name')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\Textarea::make('composition')
                            ->label('Composition')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('brand')
                            ->label('Brand')
                            ->translateLabel()
                            ->formatStateUsing(fn($state) => ucfirst($state))
                            ->disabled(),
                        Forms\Components\TextInput::make('formulation')
                            ->label('Formulation')
                            ->translateLabel()
                            ->formatStateUsing(fn($state) => ucfirst($state))
                            ->disabled(),
                        Forms\Components\TextInput::make('category')
                            ->label('Category')
                            ->translateLabel()
                            ->formatStateUsing(fn($state) => str_replace('_', ' ', ucwords($state)))
                            ->disabled(),
                        Forms\Components\TextInput::make('created_at')
                            ->label('Created At')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('updated_at')
                            ->label('Updated At')
                            ->translateLabel()
                            ->disabled(),
                    ])->columns(1))
                    ->modalWidth('3xl')
                    ->slideOver(),

                Tables\Actions\EditAction::make()
                    ->form(fn(Form $form) => $form->schema([
                        Forms\Components\TextInput::make('item_name')
                            ->label('Medication Name')
                            ->required()
                            ->maxLength(255)
                            ->inlineLabel(),
                        Forms\Components\Textarea::make('composition')
                            ->label('Composition')
                            ->maxLength(65535)
                            ->inlineLabel(),
                        Forms\Components\Select::make('brand')
                            ->label('Brand')
                            ->options([
                                'brand' => 'Brand',
                                'generic' => 'Generic',
                                'original' => 'Original',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('formulation')
                            ->label('Formulation')
                            ->options([
                                'tablet' => 'Tablet',
                                'injection' => 'Injection',
                                'capsule' => 'Capsule',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('category')
                            ->label('Category')
                            ->options([
                                'post-transplant_medications' => 'Post-Transplant Medications',
                                'diabetes_care' => 'Diabetes Care',
                                'oncology_drugs' => 'Oncology Drugs',
                                'cardiovascular' => 'Cardiovascular',
                                'mens_health' => 'Men\'s Health',
                                'renal' => 'Renal',
                            ])
                            ->required()
                            ->inlineLabel(),
                    ])->columns(1))
                    ->modalWidth('3xl')
                    ->slideOver(),

                Tables\Actions\Action::make('restore')
                    ->label('Restore')
                    ->action(fn(Model $record) => $record->restore())
                    ->visible(fn(Model $record) => $record->trashed())
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Restore Medication Record')
                    ->modalSubheading('Are you sure you want to restore this medication record?'),

                Tables\Actions\Action::make('forceDelete')
                    ->label('Delete')
                    ->action(fn(Model $record) => $record->safeForceDelete())
                    ->visible(fn(Model $record) => $record->trashed())
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Permanently Delete Medication Record')
                    ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),

                Tables\Actions\DeleteAction::make()
                    ->label('Deactivate')
                    ->visible(fn(Model $record) => !$record->trashed())
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate Medication Record')
                    ->modalSubheading('The medication record will be moved to the deactivated list. You can restore it later.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Deactivate')
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Medication Records')
                        ->modalSubheading('The selected medication records will be moved to the deactivated list. You can restore them later.'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Restore')
                        ->requiresConfirmation()
                        ->modalHeading('Restore Medication Records')
                        ->modalSubheading('Are you sure you want to restore the selected medication records?'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Delete')
                        ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Medication Records')
                        ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),
                ]),
            ])
            ->defaultSort('item_name', 'asc')
            ->persistFiltersInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedications::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }
}