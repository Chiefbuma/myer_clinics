<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NutritionResource\Pages;
use App\Models\Nutrition;
use App\Models\Scheme;
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

class NutritionResource extends Resource
{
    protected static ?string $model = Nutrition::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Assesments';
    protected static ?string $modelLabel = 'Nutrition Report';
    protected static ?string $navigationLabel = 'Nutrition';

    public static function getModelLabel(): string
    {
        return __('Nutrition');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Nutrition');
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
                            ->translateLabel()
                            ->inlineLabel(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Nutrition Assessment')
                    ->schema([
                        Forms\Components\Select::make('scheme_id')
                            ->label('Scheme')
                            ->options(function () {
                                return Scheme::pluck('scheme_name', 'scheme_id');
                            })
                            ->required()
                            ->inlineLabel(),

                        Forms\Components\DatePicker::make('last_visit')
                            ->label('Last Visit')
                            ->required()
                            ->inlineLabel(),

                        Forms\Components\DatePicker::make('next_review')
                            ->label('Next Review')
                            ->required()
                            ->inlineLabel(),

                        Forms\Components\TextInput::make('muscle_mass')
                            ->label('Muscle Mass')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),

                        Forms\Components\TextInput::make('bone_mass')
                            ->label('Bone Mass')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),

                        Forms\Components\TextInput::make('weight')
                            ->label('Weight')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),

                        Forms\Components\TextInput::make('BMI')
                            ->label('BMI')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),

                        Forms\Components\TextInput::make('total_body_fat')
                            ->label('Total body fat')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),

                        Forms\Components\TextInput::make('visceral_fat')
                            ->label('Visceral Fat')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),

                        Forms\Components\Textarea::make('weight_remarks')
                            ->label('Weight Remarks')
                            ->required()
                            ->inlineLabel(),

                        Forms\Components\Select::make('physical_activity')
                            ->label('Physical Activity')
                            ->options([
                                'active' => 'Active',
                                'moderate' => 'Moderate',
                                'sedentary' => 'Sedentary',
                            ])
                            ->required()
                            ->inlineLabel(),

                        Forms\Components\Select::make('meal_plan_set_up')
                            ->label('Meal Plan Set Up')
                            ->options([
                                'yes' => 'Yes',
                                'no' => 'No',
                            ])
                            ->required()
                            ->inlineLabel(),

                        Forms\Components\Select::make('nutrition_adherence')
                            ->label('Nutrition Adherence')
                            ->options([
                                'compliant' => 'Compliant',
                                'non_compliant' => 'Non-Compliant',
                            ])
                            ->required()
                            ->inlineLabel(),

                        Forms\Components\Textarea::make('nutrition_assessment_remarks')
                            ->label('Nutrition Assessment Remarks')
                            ->required()
                            ->inlineLabel(),

                        Forms\Components\TextInput::make('revenue')
                            ->label('Revenue')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),

                        Forms\Components\DatePicker::make('visit_date')
                            ->label('Visit Date')
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
                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->formatStateUsing(fn($state, $record) => $record->patient ? $record->patient->firstname . ' ' . $record->patient->lastname : 'N/A')
                    ->searchable(['firstname', 'lastname'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('scheme.scheme_name')
                    ->label('Scheme')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('last_visit')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('next_review')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('weight')
                    ->suffix(' kg')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('BMI')
                    ->label('BMI')
                    ->numeric(decimalPlaces: 1)
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('total_body_fat')
                    ->suffix('%')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('visceral_fat')
                    ->label('Visceral Fat')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('physical_activity')
                    ->limit(20)
                    ->tooltip(fn($record) => $record->physical_activity)
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('meal_plan_set_up')
                    ->formatStateUsing(fn($state) => $state === 'yes' ? 'Yes' : 'No')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('nutrition_adherence')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'compliant' => 'success',
                        'non_compliant' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('revenue')
                    ->money('KES')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('visit_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

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
                    ->modalWidth('3xl')
                    ->slideOver(),
                ExportAction::make()
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('nutrition_export_' . now()->format('Y-m-d_H-i-s'))
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
                                Column::make('last_visit')
                                    ->heading('Last Visit')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : 'N/A'),
                                Column::make('weight')
                                    ->heading('Weight (kg)'),
                                Column::make('BMI')
                                    ->heading('BMI'),
                                Column::make('total_body_fat')
                                    ->heading('Body Fat %'),
                                Column::make('visceral_fat')
                                    ->heading('Visceral Fat'),
                                Column::make('physical_activity')
                                    ->heading('Activity Level')
                                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),
                                Column::make('meal_plan_set_up')
                                    ->heading('Meal Plan')
                                    ->formatStateUsing(fn($state) => $state === 'yes' ? 'Yes' : 'No'),
                                Column::make('nutrition_adherence')
                                    ->heading('Adherence')
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'compliant' => 'Compliant',
                                        'non_compliant' => 'Non-Compliant',
                                        default => 'N/A',
                                    }),
                                Column::make('patient.scheme.scheme_name')
                                    ->heading('Scheme'),
                                Column::make('revenue')
                                    ->heading('Revenue (KES)'),
                                Column::make('next_review')
                                    ->heading('Next Review')
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
                        Forms\Components\TextInput::make('scheme_id')
                            ->label('Scheme')
                            ->translateLabel()
                            ->formatStateUsing(fn($state, $record) => $record->scheme->scheme_name)
                            ->disabled(),
                        Forms\Components\TextInput::make('last_visit')
                            ->label('Last Visit')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('next_review')
                            ->label('Next Review')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('muscle_mass')
                            ->label('Muscle Mass')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('bone_mass')
                            ->label('Bone Mass')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('weight')
                            ->label('Weight')
                            ->suffix('kg')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('BMI')
                            ->label('BMI')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('total_body_fat')
                            ->label('Total body fat')
                            ->suffix('%')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('visceral_fat')
                            ->label('Visceral Fat')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('weight_remarks')
                            ->label('Weight Remarks')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('physical_activity')
                            ->label('Physical Activity')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('meal_plan_set_up')
                            ->label('Meal Plan Set Up')
                            ->formatStateUsing(fn($state) => $state === 'yes' ? 'Yes' : 'No')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('nutrition_adherence')
                            ->label('Nutrition Adherence')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('nutrition_assessment_remarks')
                            ->label('Nutrition Assessment Remarks')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('revenue')
                            ->label('Revenue')
                            ->prefix('KES')
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
                    ])->columns(1))
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
                        Forms\Components\Select::make('scheme_id')
                            ->label('Scheme')
                            ->options(function () {
                                return Scheme::pluck('scheme_name', 'scheme_id');
                            })
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\DatePicker::make('last_visit')
                            ->label('Last Visit')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\DatePicker::make('next_review')
                            ->label('Next Review')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('muscle_mass')
                            ->label('Muscle Mass')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('bone_mass')
                            ->label('Bone Mass')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('weight')
                            ->label('Weight')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('BMI')
                            ->label('BMI')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('total_body_fat')
                            ->label('Total body fat')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('visceral_fat')
                            ->label('Visceral Fat')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Textarea::make('weight_remarks')
                            ->label('Weight Remarks')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('physical_activity')
                            ->label('Physical Activity')
                            ->options([
                                'active' => 'Active',
                                'moderate' => 'Moderate',
                                'sedentary' => 'Sedentary',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('meal_plan_set_up')
                            ->label('Meal Plan Set Up')
                            ->options([
                                'yes' => 'Yes',
                                'no' => 'No',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Select::make('nutrition_adherence')
                            ->label('Nutrition Adherence')
                            ->options([
                                'compliant' => 'Compliant',
                                'non_compliant' => 'Non-Compliant',
                            ])
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\Textarea::make('nutrition_assessment_remarks')
                            ->label('Nutrition Assessment Remarks')
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\TextInput::make('revenue')
                            ->label('Revenue')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),
                        Forms\Components\DatePicker::make('visit_date')
                            ->label('Visit Date')
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
                    ->modalHeading('Restore Nutrition Record')
                    ->modalSubheading('Are you sure you want to restore this nutrition record?'),

                Tables\Actions\Action::make('forceDelete')
                    ->label('Delete')
                    ->action(fn(Model $record) => $record->forceDelete())
                    ->visible(fn(Model $record) => $record->trashed())
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Permanently Delete Nutrition Record')
                    ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),

                Tables\Actions\DeleteAction::make()
                    ->label('Deactivate')
                    ->visible(fn(Model $record) => !$record->trashed())
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate Nutrition Record')
                    ->modalSubheading('The nutrition record will be moved to the deactivated list. You can restore it later.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Deactivate')
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Nutrition Records')
                        ->modalSubheading('The selected nutrition records will be moved to the deactivated list. You can restore them later.'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Restore')
                        ->requiresConfirmation()
                        ->modalHeading('Restore Nutrition Records')
                        ->modalSubheading('Are you sure you want to restore the selected nutrition records?'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Delete')
                         ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Nutrition Records')
                        ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),
                ]),
            ])
            ->defaultSort('visit_date', 'desc')
            ->persistFiltersInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNutrition::route('/'),
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