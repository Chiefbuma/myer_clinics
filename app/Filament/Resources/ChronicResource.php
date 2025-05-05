<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChronicResource\Pages;
use App\Models\Chronic;
use App\Models\Scheme;
use App\Models\Procedure;
use App\Models\Specialist;
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

class ChronicResource extends Resource
{
    protected static ?string $model = Chronic::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Assesments';
    protected static ?string $navigationGroupIcon = 'heroicon-o-chart-bar';

    public static function getModelLabel(): string
    {
        return __('Chronic Care');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Chronic Care');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Chronic Care Details')
                    ->schema([
                        Forms\Components\Select::make('patient_id')
                            ->relationship('patient', 'patient_no')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->firstname} {$record->lastname} (ID: {$record->patient_no})")
                            ->searchable(['firstname', 'lastname', 'patient_no'])
                            ->required()
                            ->translateLabel()
                            ->inlineLabel(),
                        Forms\Components\Select::make('procedure_id')
                            ->label('Procedure')
                            ->options(function () {
                                return Procedure::pluck('procedure_name', 'procedure_id');
                            })
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('speciality_id')
                            ->label('Specialist')
                            ->options(function () {
                                return Specialist::pluck('specialist_name', 'specialist_id');
                            })
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\DatePicker::make('refill_date')
                            ->label('Refill Date')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('compliance')
                            ->label('Compliance')
                            ->options([
                                'compliant' => 'Compliant',
                                'non_compliant' => 'Non-Compliant',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('exercise')
                            ->label('Exercise')
                            ->options([
                                'regular' => 'Regular',
                                'irregular' => 'Irregular',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('clinical_goals')
                            ->label('Clinical Goals')
                            ->options([
                                'on track' => 'On track',
                                'defaulted' => 'Defaulted',
                                'achieved' => 'Achieved',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('nutrition_follow_up')
                            ->label('Nutrition Follow-Up')
                            ->options([
                                'reviewed' => 'Reviewed',
                                'Not reveiwed' => 'Not reveiwed',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('psychosocial')
                            ->label('Psychosocial')
                            ->options([
                                'reviewed' => 'Reviewed',
                                'Not reveiwed' => 'Not reveiwed',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\DatePicker::make('annual_check_up')
                            ->label('Annual Check-Up')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\DatePicker::make('specialist_review')
                            ->label('Specialist Review')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('vitals_monitoring')
                            ->label('Vitals Monitoring')
                            ->options([
                                'stable' => 'Stable',
                                'uncontrolled' => 'Uncontrolled',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('revenue')
                            ->label('Revenue')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('remote_vital_monitor')
                            ->label('Remote Vital Monitor')
                            ->options([
                                'reviewed' => 'Reviewed',
                                'Not reveiwed' => 'Not reveiwed',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\DatePicker::make('last_visit')
                            ->label('Last Visit')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('scheme_id')
                            ->label('Scheme')
                            ->options(function () {
                                return Scheme::pluck('scheme_name', 'scheme_id');
                            })
                            ->required()
                            ->inlineLabel(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->formatStateUsing(fn($state, $record) => $record->patient->firstname . ' ' . $record->patient->lastname)
                    ->searchable(['patient.firstname', 'patient.lastname'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheme.scheme_name')
                    ->label('Scheme')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('procedure.procedure_name')
                    ->label('Procedure'),
                Tables\Columns\TextColumn::make('speciality.specialist_name')
                    ->label('Speciality'),
                Tables\Columns\TextColumn::make('refill_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('compliance'),
                Tables\Columns\TextColumn::make('exercise'),
                Tables\Columns\TextColumn::make('clinical_goals')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->clinical_goals),
                Tables\Columns\TextColumn::make('nutrition_follow_up'),
                Tables\Columns\TextColumn::make('psychosocial'),
                Tables\Columns\TextColumn::make('annual_check_up')
                    ->date(),
                Tables\Columns\TextColumn::make('specialist_review')
                    ->date(),
                Tables\Columns\TextColumn::make('vitals_monitoring'),
                Tables\Columns\TextColumn::make('revenue')
                    ->money('KES')
                    ->sortable(),
                Tables\Columns\TextColumn::make('remote_vital_monitor'),
                Tables\Columns\TextColumn::make('last_visit')
                    ->date()
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
                Tables\Filters\SelectFilter::make('patient')
                    ->relationship('patient', 'patient_no')
                    ->searchable()
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->firstname} {$record->lastname}"),
                Tables\Filters\SelectFilter::make('scheme')
                    ->relationship('scheme', 'scheme_name'),
                Tables\Filters\SelectFilter::make('procedure')
                    ->relationship('procedure', 'procedure_name'),
                Tables\Filters\Filter::make('last_visit')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('last_visit', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('last_visit', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->form(fn(Form $form) => $form->schema([
                        Forms\Components\Section::make('Chronic Care Details')
                            ->schema([
                                Forms\Components\Select::make('patient_id')
                                    ->relationship('patient', 'patient_no')
                                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->firstname} {$record->lastname} (ID: {$record->patient_no})")
                                    ->searchable(['firstname', 'lastname', 'patient_no'])
                                    ->required()
                                    ->translateLabel()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('procedure_id')
                                    ->label('Procedure')
                                    ->options(function () {
                                        return Procedure::pluck('procedure_name', 'procedure_id');
                                    })
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('speciality_id')
                                    ->label('Specialist')
                                    ->options(function () {
                                        return Specialist::pluck('specialist_name', 'specialist_id');
                                    })
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\DatePicker::make('refill_date')
                                    ->label('Refill Date')
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('compliance')
                                    ->label('Compliance')
                                    ->options([
                                        'compliant' => 'Compliant',
                                        'non_compliant' => 'Non-Compliant',
                                    ])
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('exercise')
                                    ->label('Exercise')
                                    ->options([
                                        'regular' => 'Regular',
                                        'irregular' => 'Irregular',
                                    ])
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('clinical_goals')
                                    ->label('Clinical Goals')
                                    ->options([
                                        'on track' => 'On track',
                                        'defaulted' => 'Defaulted',
                                        'achieved' => 'Achieved',
                                    ])
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('nutrition_follow_up')
                                    ->label('Nutrition Follow-Up')
                                    ->options([
                                        'reviewed' => 'Reviewed',
                                        'Not reveiwed' => 'Not reveiwed',
                                    ])
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('psychosocial')
                                    ->label('Psychosocial')
                                    ->options([
                                        'reviewed' => 'Reviewed',
                                        'Not reveiwed' => 'Not reveiwed',
                                    ])
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\DatePicker::make('annual_check_up')
                                    ->label('Annual Check-Up')
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\DatePicker::make('specialist_review')
                                    ->label('Specialist Review')
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('vitals_monitoring')
                                    ->label('Vitals Monitoring')
                                    ->options([
                                        'stable' => 'Stable',
                                        'uncontrolled' => 'Uncontrolled',
                                    ])
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('revenue')
                                    ->label('Revenue')
                                    ->numeric()
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('remote_vital_monitor')
                                    ->label('Remote Vital Monitor')
                                    ->options([
                                        'reviewed' => 'Reviewed',
                                        'Not reveiwed' => 'Not reveiwed',
                                    ])
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\DatePicker::make('last_visit')
                                    ->label('Last Visit')
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('scheme_id')
                                    ->label('Scheme')
                                    ->options(function () {
                                        return Scheme::pluck('scheme_name', 'scheme_id');
                                    })
                                    ->required()
                                    ->inlineLabel(),
                            ])
                            ->columns(1),
                    ]))
                    ->modalWidth('lg')
                    ->slideOver(),
                ExportAction::make()
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('chronic_care_export_' . now()->format('Y-m-d_H-i-s'))
                            ->withColumns([
                                Column::make('patient.patient_no')
                                    ->heading('Patient No'),
                                Column::make('patient.full_name')
                                    ->heading('Patient Name')
                                    ->formatStateUsing(fn($state, $record) => $record->patient->firstname . ' ' . $record->patient->lastname),
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
                                Column::make('scheme.scheme_name')
                                    ->heading('Scheme'),
                                Column::make('procedure.procedure_name')
                                    ->heading('Procedure'),
                                Column::make('speciality.specialist_name')
                                    ->heading('Specialist'),
                                Column::make('refill_date')
                                    ->heading('Refill Date')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : 'N/A'),
                                Column::make('compliance')
                                    ->heading('Compliance')
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'compliant' => 'Compliant',
                                        'non_compliant' => 'Non-Compliant',
                                        default => 'N/A',
                                    }),
                                Column::make('exercise')
                                    ->heading('Exercise')
                                    ->formatStateUsing(fn($state) => ucfirst($state)),
                                Column::make('clinical_goals')
                                    ->heading('Clinical Goals'),
                                Column::make('nutrition_follow_up')
                                    ->heading('Nutrition Follow-Up'),
                                Column::make('psychosocial')
                                    ->heading('Psychosocial'),
                                Column::make('annual_check_up')
                                    ->heading('Annual Check-Up')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : 'N/A'),
                                Column::make('specialist_review')
                                    ->heading('Specialist Review')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : 'N/A'),
                                Column::make('vitals_monitoring')
                                    ->heading('Vitals Monitoring'),
                                Column::make('revenue')
                                    ->heading('Revenue (KES)')
                                    ->formatStateUsing(fn($state) => number_format($state, 2)),
                                Column::make('remote_vital_monitor')
                                    ->heading('Remote Vital Monitor'),
                                Column::make('last_visit')
                                    ->heading('Last Visit')
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
                        Forms\Components\Section::make('Chronic Care Details')
                            ->schema([
                                Forms\Components\TextInput::make('patient_id')
                                    ->label('Patient')
                                    ->formatStateUsing(fn($state, $record) => $record->patient->firstname . ' ' . $record->patient->lastname)
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('procedure_id')
                                    ->label('Procedure')
                                    ->formatStateUsing(fn($state, $record) => $record->procedure->procedure_name ?? '')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('speciality_id')
                                    ->label('Specialist')
                                    ->formatStateUsing(fn($state, $record) => $record->speciality->specialist_name ?? '')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('refill_date')
                                    ->label('Refill Date')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('compliance')
                                    ->label('Compliance')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('exercise')
                                    ->label('Exercise')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('clinical_goals')
                                    ->label('Clinical Goals')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('nutrition_follow_up')
                                    ->label('Nutrition Follow-Up')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('psychosocial')
                                    ->label('Psychosocial')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('annual_check_up')
                                    ->label('Annual Check-Up')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('specialist_review')
                                    ->label('Specialist Review')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('vitals_monitoring')
                                    ->label('Vitals Monitoring')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('revenue')
                                    ->label('Revenue')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('remote_vital_monitor')
                                    ->label('Remote Vital Monitor')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('last_visit')
                                    ->label('Last Visit')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('scheme_id')
                                    ->label('Scheme')
                                    ->formatStateUsing(fn($state, $record) => $record->scheme->scheme_name ?? '')
                                    ->disabled()
                                    ->inlineLabel(),
                            ])
                            ->columns(1),
                    ]))
                    ->modalWidth('lg')
                    ->slideOver(),

                Tables\Actions\EditAction::make()
                    ->form(fn(Form $form) => $form->schema([
                        Forms\Components\Section::make('Chronic Care Details')
                            ->schema([
                                Forms\Components\Select::make('patient_id')
                                    ->relationship('patient', 'patient_no')
                                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->firstname} {$record->lastname} (ID: {$record->patient_no})")
                                    ->searchable(['firstname', 'lastname', 'patient_no'])
                                    ->required()
                                    ->translateLabel()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('procedure_id')
                                    ->label('Procedure')
                                    ->options(function () {
                                        return Procedure::pluck('procedure_name', 'procedure_id');
                                    })
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('speciality_id')
                                    ->label('Specialist')
                                    ->options(function () {
                                        return Specialist::pluck('specialist_name', 'specialist_id');
                                    })
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\DatePicker::make('refill_date')
                                    ->label('Refill Date')
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('compliance')
                                    ->label('Compliance')
                                    ->options([
                                        'compliant' => 'Compliant',
                                        'non_compliant' => 'Non-Compliant',
                                    ])
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('exercise')
                                    ->label('Exercise')
                                    ->options([
                                        'regular' => 'Regular',
                                        'irregular' => 'Irregular',
                                    ])
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('clinical_goals')
                                    ->label('Clinical Goals')
                                    ->options([
                                        'on track' => 'On track',
                                        'defaulted' => 'Defaulted',
                                        'achieved' => 'Achieved',
                                    ])
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('nutrition_follow_up')
                                    ->label('Nutrition Follow-Up')
                                    ->options([
                                        'reviewed' => 'Reviewed',
                                        'Not reveiwed' => 'Not reveiwed',
                                    ])
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('psychosocial')
                                    ->label('Psychosocial')
                                    ->options([
                                        'reviewed' => 'Reviewed',
                                        'Not reveiwed' => 'Not reveiwed',
                                    ])
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\DatePicker::make('annual_check_up')
                                    ->label('Annual Check-Up')
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\DatePicker::make('specialist_review')
                                    ->label('Specialist Review')
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('vitals_monitoring')
                                    ->label('Vitals Monitoring')
                                    ->options([
                                        'stable' => 'Stable',
                                        'uncontrolled' => 'Uncontrolled',
                                    ])
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('revenue')
                                    ->label('Revenue')
                                    ->numeric()
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('remote_vital_monitor')
                                    ->label('Remote Vital Monitor')
                                    ->options([
                                        'reviewed' => 'Reviewed',
                                        'Not reveiwed' => 'Not reveiwed',
                                    ])
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\DatePicker::make('last_visit')
                                    ->label('Last Visit')
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('scheme_id')
                                    ->label('Scheme')
                                    ->options(function () {
                                        return Scheme::pluck('scheme_name', 'scheme_id');
                                    })
                                    ->required()
                                    ->inlineLabel(),
                            ])
                            ->columns(1),
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
                    ->modalHeading('Restore Chronic Care')
                    ->modalSubheading('Are you sure you want to restore this chronic care record?'),

                Tables\Actions\Action::make('forceDelete')
                    ->label('Delete')
                    ->action(fn(Model $record) => $record->forceDelete())
                    ->visible(fn(Model $record) => $record->trashed())
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Permanently Delete Chronic Care')
                    ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),

                Tables\Actions\DeleteAction::make()
                    ->label('Deactivate')
                    ->visible(fn(Model $record) => !$record->trashed())
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate Chronic Care')
                    ->modalSubheading('The chronic care record will be moved to the deactivated list. You can restore it later.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Deactivate')
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Chronic Care Records')
                        ->modalSubheading('The selected chronic care records will be moved to the deactivated list. You can restore them later.'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Restore')
                        ->requiresConfirmation()
                        ->modalHeading('Restore Chronic Care Records')
                        ->modalSubheading('Are you sure you want to restore the selected chronic care records?'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Delete')
                        ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Chronic Care Records')
                        ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),
                ]),
            ])
            ->defaultSort('last_visit', 'desc')
            ->persistFiltersInSession();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChronics::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->with([
                'patient.cohort',
                'patient.branch',
                'patient.scheme',
                'patient.route',
                'patient.diagnoses'
            ]);
    }
}