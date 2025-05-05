<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicationUseResource\Pages;
use App\Models\Medication;
use App\Models\MedicationUse;
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

class MedicationUseResource extends Resource
{
    protected static ?string $model = MedicationUse::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Assesments';
    protected static ?string $navigationGroupIcon = 'heroicon-o-chart-bar';

    public static function getModelLabel(): string
    {
        return __('Medication Use');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Medication Use');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Medication Use Details')
                    ->schema([
                        Forms\Components\Select::make('patient_id')
                            ->relationship('patient', 'patient_no')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->firstname} {$record->lastname} (ID: {$record->patient_no})")
                            ->searchable(['firstname', 'lastname', 'patient_no'])
                            ->required()
                            ->translateLabel()
                            ->inlineLabel(),

                        Forms\Components\Select::make('medication_id')
                            ->label('Medication')
                            ->relationship('medication', 'item_name')
                            ->searchable(['item_name'])
                            ->required()
                            ->rules('exists:medication,medication_id')
                            ->translateLabel()
                            ->inlineLabel(),

                        Forms\Components\TextInput::make('days_supplied')
                            ->label('Days Supplied')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->rules('numeric|min:1')
                            ->translateLabel()
                            ->inlineLabel(),

                        Forms\Components\TextInput::make('no_pills_dispensed')
                            ->label('No. of Pills Dispensed')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->rules('numeric|min:1')
                            ->translateLabel()
                            ->inlineLabel(),

                        Forms\Components\Select::make('frequency')
                            ->label('Frequency')
                            ->options([
                                'daily' => 'Daily',
                                'weekly' => 'Weekly',
                                'monthly' => 'Monthly',
                            ])
                            ->required()
                            ->rules('in:daily,weekly,monthly')
                            ->translateLabel()
                            ->inlineLabel(),

                        Forms\Components\DatePicker::make('visit_date')
                            ->label('Visit Date')
                            ->required()
                            ->rules('date')
                            ->translateLabel()
                            ->inlineLabel(),
                    ])
                    ->columns(2),

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
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('medication_use_id')
                    ->label('Medication Use ID')
                    ->translateLabel()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->translateLabel()
                    ->formatStateUsing(fn($state, $record) => $record->patient->firstname . ' ' . $record->patient->lastname)
                    ->searchable(['patient.firstname', 'patient.lastname'])
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('medication.item_name')
                    ->label('Medication')
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('days_supplied')
                    ->label('Days Supplied')
                    ->translateLabel()
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('no_pills_dispensed')
                    ->label('No. of Pills Dispensed')
                    ->translateLabel()
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('frequency')
                    ->label('Frequency')
                    ->translateLabel()
                    ->sortable(),

                Tables\Columns\TextColumn::make('visit_date')
                    ->label('Visit Date')
                    ->translateLabel()
                    ->date()
                    ->sortable(),

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
                Tables\Filters\Filter::make('visit_date')
                    ->form([
                        Forms\Components\DatePicker::make('visit_from')
                            ->label('From')
                            ->translateLabel(),
                        Forms\Components\DatePicker::make('visit_until')
                            ->label('Until')
                            ->default(now())
                            ->translateLabel(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['visit_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('visit_date', '>=', $date)
                            )
                            ->when(
                                $data['visit_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('visit_date', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['visit_from']) {
                            $indicators[] = 'Visit from: ' . $data['visit_from'];
                        }
                        if ($data['visit_until']) {
                            $indicators[] = 'Visit until: ' . $data['visit_until'];
                        }
                        return $indicators;
                    })
                    ->translateLabel(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->form(fn(Form $form) => $form->schema([
                        Forms\Components\Select::make('patient_id')
                            ->relationship('patient', 'patient_no')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->firstname} {$record->lastname} (ID: {$record->patient_no})")
                            ->searchable(['firstname', 'lastname', 'patient_no'])
                            ->required()
                            ->translateLabel()
                            ->inlineLabel(),
                        Forms\Components\Section::make('Drug Records')
                            ->schema([
                                Forms\Components\Repeater::make('drug_records')
                                    ->label('Drug Records')
                                    ->required()
                                    ->schema([
                                        Forms\Components\Select::make('medication_id')
                                            ->label('Medication')
                                            ->relationship('medication', 'item_name')
                                            ->searchable(['item_name'])
                                            ->required()
                                            ->rules('exists:medication,medication_id')
                                            ->extraInputAttributes(['class' => 'w-48'])
                                            ->columnSpan(1),
                                        Forms\Components\TextInput::make('days_supplied')
                                            ->label('Days Supplied')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->rules('numeric|min:1')
                                            ->extraInputAttributes(['class' => 'w-48'])
                                            ->columnSpan(1),
                                        Forms\Components\TextInput::make('no_pills_dispensed')
                                            ->label('No. of Pills Dispensed')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->rules('numeric|min:1')
                                            ->extraInputAttributes(['class' => 'w-48'])
                                            ->columnSpan(1),
                                        Forms\Components\Select::make('frequency')
                                            ->label('Frequency')
                                            ->options([
                                                'daily' => 'Daily',
                                                'weekly' => 'Weekly',
                                                'monthly' => 'Monthly',
                                            ])
                                            ->required()
                                            ->rules('in:daily,weekly,monthly')
                                            ->extraInputAttributes(['class' => 'w-48'])
                                            ->columnSpan(1),
                                        Forms\Components\DatePicker::make('visit_date')
                                            ->label('Visit Date')
                                            ->required()
                                            ->rules('date')
                                            ->extraInputAttributes(['class' => 'w-48'])
                                            ->columnSpan(1),
                                    ])
                                    ->columns(5)
                                    ->createItemButtonLabel('Add Drug Record')
                                    ->collapsible()
                                    ->cloneable()
                                    ->itemLabel(fn(array $state): ?string => Medication::find($state['medication_id'] ?? null)?->item_name . ' - ' . ($state['frequency'] ?? '')),
                            ]),
                    ]))
                    ->modalWidth('6xl')
                    ->slideOver()
                    ->action(function (array $data, Tables\Actions\CreateAction $action) {
                        // Get the patient_id
                        $patientId = $data['patient_id'];

                        // Check if drug_records exists and is an array
                        if (!isset($data['drug_records']) || !is_array($data['drug_records']) || empty($data['drug_records'])) {
                            $action->failureNotificationMessage('At least one drug record is required.');
                            $action->failure();
                            return;
                        }

                        // Create a new MedicationUse record for each drug record
                        foreach ($data['drug_records'] as $drugRecord) {
                            MedicationUse::create([
                                'patient_id' => $patientId,
                                'medication_id' => $drugRecord['medication_id'],
                                'days_supplied' => $drugRecord['days_supplied'],
                                'no_pills_dispensed' => $drugRecord['no_pills_dispensed'],
                                'frequency' => $drugRecord['frequency'],
                                'visit_date' => $drugRecord['visit_date'],
                            ]);
                        }

                        $action->successNotificationMessage('Medication use records created successfully.');
                        $action->success();
                    }),
                    ExportAction::make()
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable() // This is the key change - use fromTable() instead of manually handling filters
                            ->withFilename('medication_use_export_' . now()->format('Y-m-d_H-i-s'))
                            ->withColumns([
                                Column::make('patient.patient_no')
                                    ->heading('Patient No'),
                                Column::make('patient.dob')
                                    ->heading('Date of Birth')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : 'N/A'),
                                Column::make('patient.gender')
                                    ->heading('Gender'),
                                Column::make('patient.phone_no')
                                    ->heading('Phone'),
                                Column::make('patient.email')
                                    ->heading('Email'),
                                Column::make('patient.patient_status')
                                    ->heading('Status'),
                                Column::make('patient.cohort.cohort_name')
                                    ->heading('Cohort'),
                                Column::make('patient.branch.branch_name')
                                    ->heading('Branch'),
                                Column::make('patient.route.route_name')
                                    ->heading('Route'),
                                Column::make('patient.diagnoses')
                                    ->heading('Diagnoses')
                                    ->formatStateUsing(function ($state) {
                                        return $state->isNotEmpty() 
                                            ? $state->pluck('diagnosis_name')->implode(', ') 
                                            : 'N/A';
                                    }),
                                Column::make('medication.item_name')
                                    ->heading('Medication'),
                                Column::make('days_supplied')
                                    ->heading('Days Supplied'),
                                Column::make('no_pills_dispensed')
                                    ->heading('No. of Pills Dispensed'),
                                Column::make('frequency')
                                    ->heading('Frequency')
                                    ->formatStateUsing(fn($state) => ucfirst($state)),
                                Column::make('visit_date')
                                    ->heading('Visit Date')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : 'N/A'),
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
                        Forms\Components\TextInput::make('patient_id')
                            ->label('Patient')
                            ->translateLabel()
                            ->formatStateUsing(fn($state, $record) => $record->patient->firstname . ' ' . $record->patient->lastname)
                            ->disabled(),
                        Forms\Components\TextInput::make('medication_id')
                            ->label('Medication')
                            ->translateLabel()
                            ->formatStateUsing(fn($state, $record) => $record->medication->item_name)
                            ->disabled(),
                        Forms\Components\TextInput::make('days_supplied')
                            ->label('Days Supplied')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('no_pills_dispensed')
                            ->label('No. of Pills Dispensed')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('frequency')
                            ->label('Frequency')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('visit_date')
                            ->label('Visit Date')
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
                    ->modalWidth('3xl')
                    ->slideOver(),

                Tables\Actions\EditAction::make()
                    ->form(fn(Form $form) => $form->schema([
                        Forms\Components\Select::make('patient_id')
                            ->relationship('patient', 'patient_no')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->firstname} {$record->lastname} (ID: {$record->patient_no})")
                            ->searchable(['firstname', 'lastname', 'patient_no'])
                            ->required()
                            ->translateLabel()
                            ->inlineLabel(),
                        Forms\Components\Select::make('medication_id')
                            ->label('Medication')
                            ->relationship('medication', 'item_name')
                            ->searchable(['item_name'])
                            ->required()
                            ->rules('exists:medication,medication_id')
                            ->translateLabel()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('days_supplied')
                            ->label('Days Supplied')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->rules('numeric|min:1')
                            ->translateLabel()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('no_pills_dispensed')
                            ->label('No. of Pills Dispensed')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->rules('numeric|min:1')
                            ->translateLabel()
                            ->inlineLabel(),
                        Forms\Components\Select::make('frequency')
                            ->label('Frequency')
                            ->options([
                                'daily' => 'Daily',
                                'weekly' => 'Weekly',
                                'monthly' => 'Monthly',
                            ])
                            ->required()
                            ->rules('in:daily,weekly,monthly')
                            ->translateLabel()
                            ->inlineLabel(),
                        Forms\Components\DatePicker::make('visit_date')
                            ->label('Visit Date')
                            ->required()
                            ->rules('date')
                            ->translateLabel()
                            ->inlineLabel(),
                    ]))
                    ->modalWidth('3xl')
                    ->slideOver(),

                Tables\Actions\Action::make('restore')
                    ->label('Restore')
                    ->action(fn(Model $record) => $record->restore())
                    ->visible(fn(Model $record) => $record->trashed())
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Restore Medication Use')
                    ->modalSubheading('Are you sure you want to restore this medication use record?'),

                Tables\Actions\Action::make('forceDelete')
                    ->label('Delete')
                    ->action(fn(Model $record) => $record->forceDelete())
                    ->visible(fn(Model $record) => $record->trashed())
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Permanently Delete Medication Use')
                    ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),

                Tables\Actions\DeleteAction::make()
                    ->label('Deactivate')
                    ->visible(fn(Model $record) => !$record->trashed())
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate Medication Use')
                    ->modalSubheading('The medication use record will be moved to the deactivated list. You can restore it later.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Deactivate')
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Medication Use Records')
                        ->modalSubheading('The selected medication use records will be moved to the deactivated list. You can restore them later.'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Restore')
                        ->requiresConfirmation()
                        ->modalHeading('Restore Medication Use Records')
                        ->modalSubheading('Are you sure you want to restore the selected medication use records?'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Delete')
                        ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Medication Use Records')
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
            'index' => Pages\ListMedicationUses::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes();
    }
}