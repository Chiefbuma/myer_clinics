<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhysiotherapyResource\Pages;
use App\Models\Physiotherapy;
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

class PhysiotherapyResource extends Resource
{
    protected static ?string $model = Physiotherapy::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Assesments';
    protected static ?string $navigationGroupIcon = 'heroicon-o-document-report';

    public static function getModelLabel(): string
    {
        return __('Physiotherapy');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Physiotherapy');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Patient Details')
                    ->schema([
                        Forms\Components\Select::make('patient_id')
                            ->relationship('patient', 'patient_no')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->firstname} {$record->lastname} (ID: {$record->patient_no})")
                            ->searchable(['firstname', 'lastname', 'patient_no'])
                            ->required()
                            ->translateLabel(),
                        Forms\Components\Select::make('scheme_id')
                            ->relationship('scheme', 'scheme_name')
                            ->required()
                            ->translateLabel(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Physiotherapy Assessment')
                    ->schema([
                        Forms\Components\DatePicker::make('visit_date')
                            ->label('Visit Date')
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('pain_level')
                            ->label('Pain Level (Scale 0-10)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('mobility_score')
                            ->label('Mobility Score (1-5)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('range_of_motion')
                            ->label('Range of Motion (1-5)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('strength')
                            ->label('Strength (0-5)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('balance')
                            ->label('Balance (1-5)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('walking_ability')
                            ->label('Walking Ability (0-60 min)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(60)
                            ->required()
                            ->suffix(' mins')
                            ->translateLabel(),
                        Forms\Components\Select::make('posture_assessment')
                            ->label('Posture Assessment')
                            ->options([
                                'kyphosis' => 'Kyphosis',
                                'scoliosis' => 'Scoliosis',
                                'anterior_pelvic_tilt' => 'Anterior Pelvic Tilt',
                                'lordosis' => 'Lordosis',
                                'normal' => 'Normal',
                            ])
                            ->required()
                            ->translateLabel(),
                        Forms\Components\Select::make('exercise_type')
                            ->label('Exercise Type')
                            ->options([
                                'aerobic' => 'Aerobic',
                                'strengthening' => 'Strengthening',
                                'flexibility' => 'Flexibility',
                                'balance' => 'Balance',
                                'functional' => 'Functional',
                            ])
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('frequency_per_week')
                            ->label('Frequency (Per Week)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(7)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('duration_per_session')
                            ->label('Duration of Each Session (Minutes)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(60)
                            ->required()
                            ->suffix(' mins')
                            ->translateLabel(),
                        Forms\Components\TextInput::make('intensity')
                            ->label('Intensity (1-10)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('pain_level_before_exercise')
                            ->label('Pain Level Before Exercise (0-10)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('pain_level_after_exercise')
                            ->label('Pain Level After Exercise (0-10)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('fatigue_level_before_exercise')
                            ->label('Fatigue Level Before Exercise (0-10)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('fatigue_level_after_exercise')
                            ->label('Fatigue Level After Exercise (0-10)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('post_exercise_recovery_time')
                            ->label('Post-Exercise Recovery Time (1-60 min)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(60)
                            ->required()
                            ->suffix(' mins')
                            ->translateLabel(),
                        Forms\Components\TextInput::make('functional_independence')
                            ->label('Functional Independence (1-5)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\Select::make('joint_swelling')
                            ->label('Joint Swelling/Inflammation')
                            ->options([
                                1 => 'Yes',
                                0 => 'No',
                            ])
                            ->required()
                            ->translateLabel(),
                        Forms\Components\Select::make('muscle_spasms')
                            ->label('Muscle Spasms')
                            ->options([
                                1 => 'Yes',
                                0 => 'No',
                            ])
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('progress')
                            ->label('Progress (0-5)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\Textarea::make('treatment')
                            ->label('Treatment')
                            ->required()
                            ->translateLabel(),
                        Forms\Components\Textarea::make('challenges')
                            ->label('Challenges')
                            ->required()
                            ->translateLabel(),
                        Forms\Components\Textarea::make('adjustments_made')
                            ->label('Adjustments Made')
                            ->translateLabel(),
                        Forms\Components\TextInput::make('calcium_levels')
                            ->label('Calcium Levels')
                            ->numeric()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('phosphorous_levels')
                            ->label('Phosphorous Levels')
                            ->numeric()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('vit_d_levels')
                            ->label('Vitamin D Levels')
                            ->numeric()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('cholesterol_levels')
                            ->label('Cholesterol Levels')
                            ->numeric()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('iron_levels')
                            ->label('Iron Levels')
                            ->numeric()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('heart_rate')
                            ->label('Heart Rate')
                            ->numeric()
                            ->suffix(' bpm')
                            ->translateLabel(),
                        Forms\Components\TextInput::make('blood_pressure_systolic')
                            ->label('BP Systolic')
                            ->numeric()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('blood_pressure_diastolic')
                            ->label('BP Diastolic')
                            ->numeric()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('oxygen_saturation')
                            ->label('Oxygen Saturation')
                            ->numeric()
                            ->suffix('%')
                            ->translateLabel(),
                        Forms\Components\TextInput::make('hydration_level')
                            ->label('Hydration Level')
                            ->numeric()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('sleep_quality')
                            ->label('Sleep Quality')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->translateLabel(),
                        Forms\Components\TextInput::make('stress_level')
                            ->label('Stress Level')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->translateLabel(),
                        Forms\Components\TextInput::make('medication_usage')
                            ->label('Medication Usage')
                            ->translateLabel(),
                        Forms\Components\Textarea::make('therapist_notes')
                            ->label('Therapist Notes')
                            ->translateLabel(),
                        Forms\Components\TextInput::make('revenue')
                            ->label('Revenue')
                            ->numeric()
                            ->prefix('KES')
                            ->translateLabel(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Timestamps')
                    ->schema([
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
                Tables\Columns\TextColumn::make('id')
                    ->label('Physiotherapy ID')
                    ->translateLabel()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->formatStateUsing(fn($state, $record) => $record->patient ? $record->patient->firstname . ' ' . $record->patient->lastname : 'N/A')
                    ->searchable(['firstname', 'lastname'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheme.scheme_name')
                    ->label('Scheme')
                    ->formatStateUsing(fn($state, $record) => $record->scheme ? $record->scheme->scheme_name : 'N/A')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('visit_date')
                    ->label('Visit Date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pain_level')
                    ->label('Pain Level')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mobility_score')
                    ->label('Mobility Score')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('range_of_motion')
                    ->label('Range of Motion')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('strength')
                    ->label('Strength')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Balance')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('walking_ability')
                    ->label('Walking Ability')
                    ->numeric()
                    ->suffix(' mins')
                    ->sortable(),
                Tables\Columns\TextColumn::make('posture_assessment')
                    ->label('Posture Assessment')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('exercise_type')
                    ->label('Exercise Type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('frequency_per_week')
                    ->label('Frequency/Week')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_per_session')
                    ->label('Duration/Session')
                    ->numeric()
                    ->suffix(' mins')
                    ->sortable(),
                Tables\Columns\TextColumn::make('intensity')
                    ->label('Intensity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pain_level_before_exercise')
                    ->label('Pain Before Exercise')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pain_level_after_exercise')
                    ->label('Pain After Exercise')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fatigue_level_before_exercise')
                    ->label('Fatigue Before Exercise')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fatigue_level_after_exercise')
                    ->label('Fatigue After Exercise')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('post_exercise_recovery_time')
                    ->label('Recovery Time')
                    ->numeric()
                    ->suffix(' mins')
                    ->sortable(),
                Tables\Columns\TextColumn::make('functional_independence')
                    ->label('Functional Independence')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('joint_swelling')
                    ->label('Joint Swelling')
                    ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No')
                    ->sortable(),
                Tables\Columns\TextColumn::make('muscle_spasms')
                    ->label('Muscle Spasms')
                    ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No')
                    ->sortable(),
                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('treatment')
                    ->label('Treatment')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) <= 50 ? null : $state;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('challenges')
                    ->label('Challenges')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) <= 50 ? null : $state;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('adjustments_made')
                    ->label('Adjustments Made')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) <= 50 ? null : $state;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('calcium_levels')
                    ->label('Calcium Levels')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phosphorous_levels')
                    ->label('Phosphorous Levels')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vit_d_levels')
                    ->label('Vitamin D Levels')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cholesterol_levels')
                    ->label('Cholesterol Levels')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('iron_levels')
                    ->label('Iron Levels')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('heart_rate')
                    ->label('Heart Rate')
                    ->numeric()
                    ->suffix(' bpm')
                    ->sortable(),
                Tables\Columns\TextColumn::make('blood_pressure_systolic')
                    ->label('BP Systolic')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('blood_pressure_diastolic')
                    ->label('BP Diastolic')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('oxygen_saturation')
                    ->label('Oxygen Saturation')
                    ->numeric()
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hydration_level')
                    ->label('Hydration Level')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sleep_quality')
                    ->label('Sleep Quality')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stress_level')
                    ->label('Stress Level')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('medication_usage')
                    ->label('Medication Usage')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) <= 50 ? null : $state;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('therapist_notes')
                    ->label('Therapist Notes')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) <= 50 ? null : $state;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('revenue')
                    ->label('Revenue')
                    ->numeric()
                    ->prefix('KES ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable(),
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
                        Forms\Components\Select::make('patient_id')
                            ->relationship('patient', 'patient_no')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->firstname} {$record->lastname} (ID: {$record->patient_no})")
                            ->searchable(['firstname', 'lastname', 'patient_no'])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('scheme_id')
                            ->relationship('scheme', 'scheme_name')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\DatePicker::make('visit_date')
                            ->label('Visit Date')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('pain_level')
                            ->label('Pain Level (Scale 0-10)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('mobility_score')
                            ->label('Mobility Score (1-5)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('range_of_motion')
                            ->label('Range of Motion (1-5)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('strength')
                            ->label('Strength (0-5)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5)
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('balance')
                            ->label('Balance (1-5)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('walking_ability')
                            ->label('Walking Ability (0-60 min)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(60)
                            ->required()
                            ->inlineLabel()
                            ->suffix(' mins'),
                        Forms\Components\Select::make('posture_assessment')
                            ->label('Posture Assessment')
                            ->options([
                                'kyphosis' => 'Kyphosis',
                                'scoliosis' => 'Scoliosis',
                                'anterior_pelvic_tilt' => 'Anterior Pelvic Tilt',
                                'lordosis' => 'Lordosis',
                                'normal' => 'Normal',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('exercise_type')
                            ->label('Exercise Type')
                            ->options([
                                'aerobic' => 'Aerobic',
                                'strengthening' => 'Strengthening',
                                'flexibility' => 'Flexibility',
                                'balance' => 'Balance',
                                'functional' => 'Functional',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('frequency_per_week')
                            ->label('Frequency (Per Week)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(7)
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('duration_per_session')
                            ->label('Duration of Each Session (Minutes)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(60)
                            ->required()
                            ->inlineLabel()
                            ->suffix(' mins'),
                        Forms\Components\TextInput::make('intensity')
                            ->label('Intensity (1-10)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('pain_level_before_exercise')
                            ->label('Pain Level Before Exercise (0-10)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('pain_level_after_exercise')
                            ->label('Pain Level After Exercise (0-10)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('fatigue_level_before_exercise')
                            ->label('Fatigue Level Before Exercise (0-10)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('fatigue_level_after_exercise')
                            ->label('Fatigue Level After Exercise (0-10)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('post_exercise_recovery_time')
                            ->label('Post-Exercise Recovery Time (1-60 min)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(60)
                            ->suffix(' mins')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('functional_independence')
                            ->label('Functional Independence (1-5)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('joint_swelling')
                            ->label('Joint Swelling/Inflammation')
                            ->options([
                                1 => 'Yes',
                                0 => 'No',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('muscle_spasms')
                            ->label('Muscle Spasms')
                            ->options([
                                1 => 'Yes',
                                0 => 'No',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('progress')
                            ->label('Progress (0-5)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5)
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Textarea::make('treatment')
                            ->label('Treatment')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Textarea::make('challenges')
                            ->label('Challenges')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Textarea::make('adjustments_made')
                            ->label('Adjustments Made')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('calcium_levels')
                            ->label('Calcium Levels')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('phosphorous_levels')
                            ->label('Phosphorous Levels')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('vit_d_levels')
                            ->label('Vitamin D Levels')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('cholesterol_levels')
                            ->label('Cholesterol Levels')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('iron_levels')
                            ->label('Iron Levels')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('heart_rate')
                            ->label('Heart Rate')
                            ->numeric()
                            ->suffix(' bpm')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('blood_pressure_systolic')
                            ->label('BP Systolic')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('blood_pressure_diastolic')
                            ->label('BP Diastolic')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('oxygen_saturation')
                            ->label('Oxygen Saturation')
                            ->numeric()
                            ->suffix('%')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('hydration_level')
                            ->label('Hydration Level')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('sleep_quality')
                            ->label('Sleep Quality')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('stress_level')
                            ->label('Stress Level')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('medication_usage')
                            ->label('Medication Usage')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Textarea::make('therapist_notes')
                            ->label('Therapist Notes')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('revenue')
                            ->label('Revenue')
                            ->numeric()
                            ->prefix('KES')
                            ->required()
                            ->inlineLabel(),
                    ]))
                    ->modalWidth('lg')
                    ->slideOver(),
                ExportAction::make()
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        ExcelExport::make()
                            ->withFilename('physiotherapy_export_' . now()->format('Y-m-d_H-i-s'))
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
                                Column::make('visit_date')
                                    ->heading('Visit Date')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : 'N/A'),
                                Column::make('pain_level')
                                    ->heading('Pain Level'),
                                Column::make('mobility_score')
                                    ->heading('Mobility Score'),
                                Column::make('range_of_motion')
                                    ->heading('Range of Motion'),
                                Column::make('strength')
                                    ->heading('Strength'),
                                Column::make('balance')
                                    ->heading('Balance'),
                                Column::make('walking_ability')
                                    ->heading('Walking Ability'),
                                Column::make('posture_assessment')
                                    ->heading('Posture Assessment'),
                                Column::make('exercise_type')
                                    ->heading('Exercise Type'),
                                Column::make('frequency_per_week')
                                    ->heading('Frequency/Week'),
                                Column::make('duration_per_session')
                                    ->heading('Duration/Session'),
                                Column::make('intensity')
                                    ->heading('Intensity'),
                                Column::make('pain_level_before_exercise')
                                    ->heading('Pain Before Exercise'),
                                Column::make('pain_level_after_exercise')
                                    ->heading('Pain After Exercise'),
                                Column::make('fatigue_level_before_exercise')
                                    ->heading('Fatigue Before Exercise'),
                                Column::make('fatigue_level_after_exercise')
                                    ->heading('Fatigue After Exercise'),
                                Column::make('post_exercise_recovery_time')
                                    ->heading('Recovery Time'),
                                Column::make('functional_independence')
                                    ->heading('Functional Independence'),
                                Column::make('joint_swelling')
                                    ->heading('Joint Swelling')
                                    ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No'),
                                Column::make('muscle_spasms')
                                    ->heading('Muscle Spasms')
                                    ->formatStateUsing(fn($state) => $state ? 'Yes' : 'No'),
                                Column::make('progress')
                                    ->heading('Progress'),
                                Column::make('treatment')
                                    ->heading('Treatment'),
                                Column::make('challenges')
                                    ->heading('Challenges'),
                                Column::make('adjustments_made')
                                    ->heading('Adjustments Made'),
                                Column::make('calcium_levels')
                                    ->heading('Calcium Levels'),
                                Column::make('phosphorous_levels')
                                    ->heading('Phosphorous Levels'),
                                Column::make('vit_d_levels')
                                    ->heading('Vitamin D Levels'),
                                Column::make('cholesterol_levels')
                                    ->heading('Cholesterol Levels'),
                                Column::make('iron_levels')
                                    ->heading('Iron Levels'),
                                Column::make('heart_rate')
                                    ->heading('Heart Rate'),
                                Column::make('blood_pressure_systolic')
                                    ->heading('BP Systolic'),
                                Column::make('blood_pressure_diastolic')
                                    ->heading('BP Diastolic'),
                                Column::make('oxygen_saturation')
                                    ->heading('Oxygen Saturation'),
                                Column::make('hydration_level')
                                    ->heading('Hydration Level'),
                                Column::make('sleep_quality')
                                    ->heading('Sleep Quality'),
                                Column::make('stress_level')
                                    ->heading('Stress Level'),
                                Column::make('medication_usage')
                                    ->heading('Medication Usage'),
                                Column::make('therapist_notes')
                                    ->heading('Therapist Notes'),
                                Column::make('scheme.scheme_name')
                                    ->heading('Scheme'),
                                Column::make('revenue')
                                    ->heading('Revenue (KES)'),
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
                        Forms\Components\TextInput::make('id')
                            ->label('Physiotherapy ID')
                            ->translateLabel()
                            ->disabled()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('patient.full_name')
                            ->label('Patient')
                            ->formatStateUsing(fn($state, $record) => $record->patient ? $record->patient->firstname . ' ' . $record->patient->lastname : 'N/A')
                            ->translateLabel()
                            ->disabled()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('visit_date')
                            ->label('Visit Date')
                            ->translateLabel()
                            ->disabled()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('created_at')
                            ->label('Created At')
                            ->translateLabel()
                            ->disabled()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('updated_at')
                            ->label('Updated At')
                            ->translateLabel()
                            ->disabled()
                            ->inlineLabel(),
                    ]))
                    ->modalWidth('lg')
                    ->slideOver(),

                Tables\Actions\EditAction::make()
                    ->form(fn(Form $form) => $form->schema([
                        Forms\Components\Select::make('patient_id')
                            ->relationship('patient', 'patient_no')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->firstname} {$record->lastname} (ID: {$record->patient_no})")
                            ->searchable(['firstname', 'lastname', 'patient_no'])
                            ->required()
                            ->translateLabel(),
                        Forms\Components\Select::make('scheme_id')
                            ->relationship('scheme', 'scheme_name')
                            ->required()
                            ->translateLabel(),
                        Forms\Components\DatePicker::make('visit_date')
                            ->label('Visit Date')
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('pain_level')
                            ->label('Pain Level (Scale 0-10)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('mobility_score')
                            ->label('Mobility Score (1-5)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('range_of_motion')
                            ->label('Range of Motion (1-5)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('strength')
                            ->label('Strength (0-5)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('balance')
                            ->label('Balance (1-5)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('walking_ability')
                            ->label('Walking Ability (0-60 min)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(60)
                            ->required()
                            ->suffix(' mins')
                            ->translateLabel(),
                        Forms\Components\Select::make('posture_assessment')
                            ->label('Posture Assessment')
                            ->options([
                                'kyphosis' => 'Kyphosis',
                                'scoliosis' => 'Scoliosis',
                                'anterior_pelvic_tilt' => 'Anterior Pelvic Tilt',
                                'lordosis' => 'Lordosis',
                                'normal' => 'Normal',
                            ])
                            ->required()
                            ->translateLabel(),
                        Forms\Components\Select::make('exercise_type')
                            ->label('Exercise Type')
                            ->options([
                                'aerobic' => 'Aerobic',
                                'strengthening' => 'Strengthening',
                                'flexibility' => 'Flexibility',
                                'balance' => 'Balance',
                                'functional' => 'Functional',
                            ])
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('frequency_per_week')
                            ->label('Frequency (Per Week)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(7)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('duration_per_session')
                            ->label('Duration of Each Session (Minutes)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(60)
                            ->required()
                            ->suffix(' mins')
                            ->translateLabel(),
                        Forms\Components\TextInput::make('intensity')
                            ->label('Intensity (1-10)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('pain_level_before_exercise')
                            ->label('Pain Level Before Exercise (0-10)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('pain_level_after_exercise')
                            ->label('Pain Level After Exercise (0-10)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('fatigue_level_before_exercise')
                            ->label('Fatigue Level Before Exercise (0-10)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('fatigue_level_after_exercise')
                            ->label('Fatigue Level After Exercise (0-10)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('post_exercise_recovery_time')
                            ->label('Post-Exercise Recovery Time (1-60 min)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(60)
                            ->required()
                            ->suffix(' mins')
                            ->translateLabel(),
                        Forms\Components\TextInput::make('functional_independence')
                            ->label('Functional Independence (1-5)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\Select::make('joint_swelling')
                            ->label('Joint Swelling/Inflammation')
                            ->options([
                                1 => 'Yes',
                                0 => 'No',
                            ])
                            ->required()
                            ->translateLabel(),
                        Forms\Components\Select::make('muscle_spasms')
                            ->label('Muscle Spasms')
                            ->options([
                                1 => 'Yes',
                                0 => 'No',
                            ])
                            ->required()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('progress')
                            ->label('Progress (0-5)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5)
                            ->required()
                            ->translateLabel(),
                        Forms\Components\Textarea::make('treatment')
                            ->label('Treatment')
                            ->required()
                            ->translateLabel(),
                        Forms\Components\Textarea::make('challenges')
                            ->label('Challenges')
                            ->required()
                            ->translateLabel(),
                        Forms\Components\Textarea::make('adjustments_made')
                            ->label('Adjustments Made')
                            ->translateLabel(),
                        Forms\Components\TextInput::make('calcium_levels')
                            ->label('Calcium Levels')
                            ->numeric()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('phosphorous_levels')
                            ->label('Phosphorous Levels')
                            ->numeric()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('vit_d_levels')
                            ->label('Vitamin D Levels')
                            ->numeric()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('cholesterol_levels')
                            ->label('Cholesterol Levels')
                            ->numeric()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('iron_levels')
                            ->label('Iron Levels')
                            ->numeric()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('heart_rate')
                            ->label('Heart Rate')
                            ->numeric()
                            ->suffix(' bpm')
                            ->translateLabel(),
                        Forms\Components\TextInput::make('blood_pressure_systolic')
                            ->label('BP Systolic')
                            ->numeric()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('blood_pressure_diastolic')
                            ->label('BP Diastolic')
                            ->numeric()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('oxygen_saturation')
                            ->label('Oxygen Saturation')
                            ->numeric()
                            ->suffix('%')
                            ->translateLabel(),
                        Forms\Components\TextInput::make('hydration_level')
                            ->label('Hydration Level')
                            ->numeric()
                            ->translateLabel(),
                        Forms\Components\TextInput::make('sleep_quality')
                            ->label('Sleep Quality')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->translateLabel(),
                        Forms\Components\TextInput::make('stress_level')
                            ->label('Stress Level')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->translateLabel(),
                        Forms\Components\TextInput::make('medication_usage')
                            ->label('Medication Usage')
                            ->translateLabel(),
                        Forms\Components\Textarea::make('therapist_notes')
                            ->label('Therapist Notes')
                            ->translateLabel(),
                        Forms\Components\TextInput::make('revenue')
                            ->label('Revenue')
                            ->numeric()
                            ->prefix('KES')
                            ->translateLabel(),
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
                    ->modalHeading('Restore Physiotherapy Report')
                    ->modalSubheading('Are you sure you want to restore this physiotherapy report?'),

                Tables\Actions\Action::make('forceDelete')
                    ->label('Delete')
                    ->action(fn(Model $record) => $record->forceDelete())
                    ->visible(fn(Model $record) => $record->trashed())
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Permanently Delete Physiotherapy Report')
                    ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),

                Tables\Actions\DeleteAction::make()
                    ->label('Deactivate')
                    ->visible(fn(Model $record) => !$record->trashed())
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate Physiotherapy Report')
                    ->modalSubheading('The physiotherapy report will be moved to the deactivated list. You can restore it later.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Deactivate')
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Physiotherapy Reports')
                        ->modalSubheading('The selected physiotherapy reports will be moved to the deactivated list. You can restore them later.'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Restore')
                        ->requiresConfirmation()
                        ->modalHeading('Restore Physiotherapy Reports')
                        ->modalSubheading('Are you sure you want to restore the selected physiotherapy reports?'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Delete')
                        ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Physiotherapy Reports')
                        ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistFiltersInSession();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhysiotherapies::route('/'),
            //'create' => Pages\CreatePhysiotherapy::route('/create'),
            //'edit' => Pages\EditPhysiotherapy::route('/{record}/edit'),
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
                'patient.diagnoses',
                'scheme'
            ]);
    }
}