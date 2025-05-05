<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiagnosisResource\Pages;
use App\Models\Diagnosis;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class DiagnosisResource extends Resource
{
    protected static ?string $model = Diagnosis::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationGroupIcon = 'heroicon-o-cog';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    // Add the form method
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Diagnosis Details')
                    ->schema([
                        Forms\Components\Select::make('diagnosis_name')
                            ->label('Diagnosis Name')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                $suggestions = self::fetchICD10Data($search);
                                return collect($suggestions)->mapWithKeys(function ($name, $code) {
                                    $val = "{$code} - {$name}";
                                    return [$val => $val];
                                })->toArray();
                            })
                            ->getOptionLabelUsing(fn($value) => $value)
                            ->disabled(fn($context) => $context === 'view'), // Disable for view mode
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('diagnosis_name')
                    ->label('Diagnosis Name')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->form(fn(Form $form) => static::form($form)) // Reuse the form method
                    ->modalWidth('lg')
                    ->slideOver(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form(fn(Form $form) => static::form($form)) // Use the form method
                    ->modalHeading('Edit Diagnosis')
                    ->modalWidth('sm')
                    ->modalSubmitActionLabel('Save Changes')
                    ->slideOver(),

                Tables\Actions\ViewAction::make()
                    ->form(fn(Form $form) => static::form($form)) // Use the form method
                    ->modalHeading('View Diagnosis')
                    ->modalWidth('sm')
                    ->slideOver(),

                Tables\Actions\Action::make('restore')
                    ->label('Restore')
                    ->action(fn(Model $record) => $record->restore())
                    ->visible(fn(Model $record) => $record->trashed())
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Restore Diagnosis')
                    ->modalSubheading('Are you sure you want to restore this diagnosis?'),

                Tables\Actions\Action::make('forceDelete')
                    ->label('Delete')
                    ->action(fn(Model $record) => $record->forceDelete())
                    ->visible(fn(Model $record) => $record->trashed())
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Permanently Delete Diagnosis')
                    ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),

                Tables\Actions\DeleteAction::make()
                    ->label('Deactivate')
                    ->visible(fn(Model $record) => !$record->trashed())
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate Diagnosis')
                    ->modalSubheading('The diagnosis will be moved to the deactivated list. You can restore it later.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Deactivate')
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Diagnoses')
                        ->modalSubheading('The selected diagnoses will be moved to the deactivated list. You can restore them later.'),

                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Restore')
                        ->requiresConfirmation()
                        ->modalHeading('Restore Diagnoses')
                        ->modalSubheading('Are you sure you want to restore the selected diagnoses?'),

                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Delete')
                        ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Diagnoses')
                        ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Fetch ICD-10 data from the API.
     */
    protected static function fetchICD10Data(string $query): array
    {
        $url = "https://clinicaltables.nlm.nih.gov/api/icd10cm/v3/search?sf=code,name&terms=" . urlencode($query);

        try {
            $response = file_get_contents($url);
            $data = json_decode($response, true);

            if (isset($data[3]) && count($data[3]) > 0) {
                return array_combine(
                    array_column($data[3], 0),
                    array_column($data[3], 1)
                );
            }
        } catch (\Exception $e) {
            Log::error('ICD-10 API Fetch Error:', ['error' => $e->getMessage()]);
        }

        return [];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiagnoses::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes();
    }
}