<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientResource\Pages;
use App\Models\Patient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Carbon\Carbon;
use App\Models\{Diagnosis, Scheme, Route, Cohort, Branch};
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Patient details';
    protected static ?string $navigationGroupIcon = 'heroicon-o-user-group';

    public static function getModelLabel(): string
    {
        return __('Patient');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Patients');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('firstname')
                            ->label('First Name')
                            ->required()
                            ->maxLength(191)
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('lastname')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(191)
                            ->inlineLabel(),
                        Forms\Components\Select::make('gender')
                            ->options(['male' => 'Male', 'female' => 'Female'])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\DatePicker::make('dob')
                            ->label('Date of Birth')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn($set, $state) => $set('age', $state ? Carbon::parse($state)->age : null))
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('age')
                            ->label('Age')
                            ->disabled()
                            ->dehydrated()
                            ->numeric()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('location')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('phone_no')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get, $context) {
                                if ($state) {
                                    try {
                                        if ($context === 'edit') {
                                            Patient::where('phone_no', $state)
                                                ->where('patient_id', '!=', $get('patient_id'))
                                                ->firstOrFail();
                                        } else {
                                            Patient::where('phone_no', $state)->firstOrFail();
                                        }
                                        $set('phone_no', null);
                                        \Filament\Notifications\Notification::make()
                                            ->title('Phone number already exists')
                                            ->danger()
                                            ->send();
                                    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                                        // No duplicate found, proceed
                                    }
                                }
                            })
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get, $context) {
                                if ($state) {
                                    try {
                                        if ($context === 'edit') {
                                            Patient::where('email', $state)
                                                ->where('patient_id', '!=', $get('patient_id'))
                                                ->firstOrFail();
                                        } else {
                                            Patient::where('email', $state)->firstOrFail();
                                        }
                                        $set('email', null);
                                        \Filament\Notifications\Notification::make()
                                            ->title('Email already exists')
                                            ->danger()
                                            ->send();
                                    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                                        // No duplicate found, proceed
                                    }
                                }
                            })
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('patient_no')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get, $context) {
                                if ($state) {
                                    try {
                                        if ($context === 'edit') {
                                            Patient::where('patient_no', $state)
                                                ->where('patient_id', '!=', $get('patient_id'))
                                                ->firstOrFail();
                                        } else {
                                            Patient::where('patient_no', $state)->firstOrFail();
                                        }
                                        $set('patient_no', null);
                                        \Filament\Notifications\Notification::make()
                                            ->title('Patient number already exists')
                                            ->danger()
                                            ->send();
                                    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                                        // No duplicate found, proceed
                                    }
                                }
                            })
                            ->inlineLabel(),
                    ])
                    ->columns(1),

                Section::make('Medical Information')
                    ->schema([
                        Forms\Components\Select::make('diagnoses')
                            ->label('Diagnoses')
                            ->relationship('diagnoses', 'diagnosis_name')
                            ->multiple()
                            ->required()
                            ->preload()
                            ->searchable()
                            ->inlineLabel(),

                        Forms\Components\Select::make('patient_status')
                           ->relationship('patient_status', 'status')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->inlineLabel(),

                    ])
                    ->columns(1),

                Section::make('Administrative Information')
                    ->schema([
                        Forms\Components\Select::make('cohort_id')
                            ->label('Cohort')
                            ->options(Cohort::pluck('cohort_name', 'cohort_id'))
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('branch_id')
                            ->label('Branch')
                            ->options(Branch::pluck('branch_name', 'branch_id'))
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('scheme_id')
                            ->label('Scheme')
                            ->options(Scheme::pluck('scheme_name', 'scheme_id'))
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('route_id')
                            ->label('Route')
                            ->options(Route::pluck('route_name', 'route_id'))
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
                Tables\Columns\TextColumn::make('patient_id')
                    ->label('Patient ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('firstname')
                    ->label('First Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('lastname')
                    ->label('Last Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('dob')
                    ->label('Date of Birth')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_no')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('patient_no')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('patient_status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn($state) => [
                        'active' => 'success',
                        'inactive' => 'danger',
                    ][$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('cohort.cohort_name')
                    ->label('Cohort')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('branch.branch_name')
                    ->label('Branch')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('scheme.scheme_name')
                    ->label('Scheme')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('route.route_name')
                    ->label('Route')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('diagnoses.diagnosis_name')
                    ->label('Diagnoses')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('gender')
                    ->options(['male' => 'Male', 'female' => 'Female']),
                SelectFilter::make('patient_status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive']),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until Date')
                            ->default(now()),
                    ])
                    ->query(fn(Builder $query, array $data): Builder =>
                        $query
                            ->when($data['created_from'], fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn($q, $date) => $q->whereDate('created_at', '<=', $date))
                    ),
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
                            ->fromTable()
                            ->withFilename('patients_export_' . now()->format('Y-m-d_H-i-s'))
                            ->withColumns([
                                Column::make('patient_id')->heading('Patient ID'),
                                Column::make('firstname')->heading('First Name'),
                                Column::make('lastname')->heading('Last Name'),
                                Column::make('dob')
                                    ->heading('Date of Birth')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : null),
                                Column::make('gender')->heading('Gender'),
                                Column::make('location')->heading('Location'),
                                Column::make('phone_no')->heading('Phone Number'),
                                Column::make('email')->heading('Email'),
                                Column::make('patient_no')->heading('Patient Number'),
                                Column::make('patient_status')->heading('Status'),
                                Column::make('cohort.cohort_name')
                                    ->heading('Cohort')
                                    ->formatStateUsing(fn($state) => $state ?? 'N/A'),
                                Column::make('branch.branch_name')
                                    ->heading('Branch')
                                    ->formatStateUsing(fn($state) => $state ?? 'N/A'),
                                Column::make('scheme.scheme_name')
                                    ->heading('Scheme')
                                    ->formatStateUsing(fn($state) => $state ?? 'N/A'),
                                Column::make('route.route_name')
                                    ->heading('Route')
                                    ->formatStateUsing(fn($state) => $state ?? 'N/A'),
                                Column::make('diagnoses.diagnosis_name')
                                    ->heading('Diagnoses')
                                    ->formatStateUsing(fn($record) => $record->diagnoses->pluck('diagnosis_name')->join(', ')),
                                Column::make('created_at')
                                    ->heading('Created At')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i:s') : null),
                                Column::make('updated_at')
                                    ->heading('Updated At')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i:s') : null),
                                Column::make('deleted_at')
                                    ->heading('Deleted At')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i:s') : null),
                            ])
                            ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                            ->withChunkSize(1000)
                    ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->form(fn(Form $form) => $form->schema([
                        Section::make('Personal Information')
                            ->schema([
                                Forms\Components\TextInput::make('firstname')
                                    ->label('First Name')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('lastname')
                                    ->label('Last Name')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('gender')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('dob')
                                    ->label('Date of Birth')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('age')
                                    ->label('Age')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('location')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('phone_no')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('email')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('patient_no')
                                    ->disabled()
                                    ->inlineLabel(),
                            ])
                            ->columns(1),
                        Section::make('Medical Information')
                            ->schema([
                                Forms\Components\Select::make('diagnoses')
                                    ->label('Diagnoses')
                                    ->relationship('diagnoses', 'diagnosis_name')
                                    ->multiple()
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('patient_status')
                                    ->label('Status')
                                    ->disabled()
                                    ->inlineLabel(),
                            ])
                            ->columns(1),
                        Section::make('Administrative Information')
                            ->schema([
                                Forms\Components\TextInput::make('cohort.cohort_name')
                                    ->label('Cohort')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('branch.branch_name')
                                    ->label('Branch')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('scheme.scheme_name')
                                    ->label('Scheme')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('route.route_name')
                                    ->label('Route')
                                    ->disabled()
                                    ->inlineLabel(),
                            ])
                            ->columns(1),
                        Section::make('Timestamps')
                            ->schema([
                                Forms\Components\TextInput::make('created_at')
                                    ->label('Created At')
                                    ->disabled()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('updated_at')
                                    ->label('Updated At')
                                    ->disabled()
                                    ->inlineLabel(),
                            ])
                            ->columns(1),
                    ]))
                    ->modalWidth('3xl')
                    ->slideOver(),

                Tables\Actions\EditAction::make()
                    ->form(fn(Form $form) => $form->schema([
                        Section::make('Personal Information')
                            ->schema([
                                Forms\Components\TextInput::make('firstname')
                                    ->label('First Name')
                                    ->required()
                                    ->maxLength(191)
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('lastname')
                                    ->label('Last Name')
                                    ->required()
                                    ->maxLength(191)
                                    ->inlineLabel(),
                                Forms\Components\Select::make('gender')
                                    ->options(['male' => 'Male', 'female' => 'Female'])
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\DatePicker::make('dob')
                                    ->label('Date of Birth')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn($set, $state) => $set('age', $state ? Carbon::parse($state)->age : null))
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('age')
                                    ->label('Age')
                                    ->disabled()
                                    ->dehydrated()
                                    ->numeric()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('location')
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('phone_no')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get, $context) {
                                        if ($state && $context === 'edit') {
                                            try {
                                                Patient::where('phone_no', $state)
                                                    ->where('patient_id', '!=', $get('patient_id'))
                                                    ->firstOrFail();
                                                $set('phone_no', null);
                                                \Filament\Notifications\Notification::make()
                                                    ->title('Phone number already exists')
                                                    ->danger()
                                                    ->send();
                                            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                                                // No duplicate found, proceed
                                            }
                                        }
                                    })
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get, $context) {
                                        if ($state && $context === 'edit') {
                                            try {
                                                Patient::where('email', $state)
                                                    ->where('patient_id', '!=', $get('patient_id'))
                                                    ->firstOrFail();
                                                $set('email', null);
                                                \Filament\Notifications\Notification::make()
                                                    ->title('Email already exists')
                                                    ->danger()
                                                    ->send();
                                            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                                                // No duplicate found, proceed
                                            }
                                        }
                                    })
                                    ->inlineLabel(),
                                Forms\Components\TextInput::make('patient_no')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get, $context) {
                                        if ($state && $context === 'edit') {
                                            try {
                                                Patient::where('patient_no', $state)
                                                    ->where('patient_id', '!=', $get('patient_id'))
                                                    ->firstOrFail();
                                                $set('patient_no', null);
                                                \Filament\Notifications\Notification::make()
                                                    ->title('Patient number already exists')
                                                    ->danger()
                                                    ->send();
                                            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                                                // No duplicate found, proceed
                                            }
                                        }
                                    })
                                    ->inlineLabel(),
                            ])
                            ->columns(1),
                        Section::make('Medical Information')
                            ->schema([
                                Forms\Components\Select::make('diagnoses')
                                    ->label('Diagnoses')
                                    ->relationship('diagnoses', 'diagnosis_name')
                                    ->multiple()
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('patient_status')
                                    ->options([
                                        'active' => 'Active',
                                        'inactive' => 'Inactive',
                                    ])
                                    ->required()
                                    ->inlineLabel(),
                            ])
                            ->columns(1),
                        Section::make('Administrative Information')
                            ->schema([
                                Forms\Components\Select::make('cohort_id')
                                    ->label('Cohort')
                                    ->options(Cohort::pluck('cohort_name', 'cohort_id'))
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('branch_id')
                                    ->label('Branch')
                                    ->options(Branch::pluck('branch_name', 'branch_id'))
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('scheme_id')
                                    ->label('Scheme')
                                    ->options(Scheme::pluck('scheme_name', 'scheme_id'))
                                    ->required()
                                    ->inlineLabel(),
                                Forms\Components\Select::make('route_id')
                                    ->label('Route')
                                    ->options(Route::pluck('route_name', 'route_id'))
                                    ->required()
                                    ->inlineLabel(),
                            ])
                            ->columns(1),
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
                    ->modalHeading('Restore Patient')
                    ->modalSubheading('Are you sure you want to restore this patient?'),

                Tables\Actions\Action::make('forceDelete')
                    ->label('Delete')
                    ->action(fn(Model $record) => $record->forceDelete())
                    ->visible(fn(Model $record) => $record->trashed())
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Permanently Delete Patient')
                    ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),

                Tables\Actions\DeleteAction::make()
                    ->label('Deactivate')
                    ->visible(fn(Model $record) => !$record->trashed())
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate Patient')
                    ->modalSubheading('The patient will be moved to the deactivated list. You can restore it later.'),

                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Deactivate')
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Patients')
                        ->modalSubheading('The selected patients will be moved to the deactivated list. You can restore them later.'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Restore')
                        ->requiresConfirmation()
                        ->modalHeading('Restore Patients')
                        ->modalSubheading('Are you sure you want to restore the selected patients?'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Delete')
                        ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Patients')
                        ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistFiltersInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatients::route('/'),
            //'asses' => Pages\ManagePatient::route('/{record}/asses'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }
}