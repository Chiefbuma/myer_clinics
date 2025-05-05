<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PsychosocialResource\Pages;
use App\Models\Psychosocial;
use App\Models\Scheme;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;
use Maatwebsite\Excel\Excel;

class PsychosocialResource extends Resource
{
    protected static ?string $model = Psychosocial::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Assesments';
    protected static ?string $modelLabel = 'Psychosocial';
    protected static ?string $pluralModelLabel = 'Psychosocial';

    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Patient Information')
                    ->schema([
                        Forms\Components\Select::make('patient_id')
                            ->relationship('patient', 'patient_no')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->firstname} {$record->lastname} (ID: {$record->patient_no})")
                            ->searchable(['firstname', 'lastname', 'patient_no'])
                            ->required()
                            ->saveRelationshipsUsing(function ($component, $state) {
                                $component->getModelInstance()->patient_id = $state;
                            }),
                    ]),
                Section::make('Psychosocial Assessment')
                    ->schema([
                        // First Column
                        Grid::make(2)
                            ->schema([
                                // Last Visit
                                Forms\Components\DatePicker::make('last_visit')
                                    ->label('Last Visit')
                                    ->required()
                                    ->inlineLabel(),

                                // Next Review
                                Forms\Components\DatePicker::make('next_review')
                                    ->label('Next Review')
                                    ->required()
                                    ->inlineLabel(),

                                // Educational Level
                                Forms\Components\Select::make('educational_level')
                                    ->label('Educational Level')
                                    ->options([
                                        'Very Satisfied' => 'Very Satisfied',
                                        'Mildly Satisfied' => 'Mildly Satisfied',
                                        'Indifferent' => 'Indifferent',
                                        'Mildly disappointed' => 'Mildly disappointed',
                                        'Very disappointed' => 'Very disappointed',
                                    ])
                                    ->nullable()
                                    ->inlineLabel(),

                                // Career/Business
                                Forms\Components\Select::make('career_business')
                                    ->label('Career/Business')
                                    ->options([
                                        'Very Satisfied' => 'Very Satisfied',
                                        'Mildly Satisfied' => 'Mildly Satisfied',
                                        'Indifferent' => 'Indifferent',
                                        'Mildly disappointed' => 'Mildly disappointed',
                                        'Very disappointed' => 'Very disappointed',
                                    ])
                                    ->required()
                                    ->inlineLabel(),

                                // Marital Status
                                Forms\Components\Select::make('marital_status')
                                    ->label('Marital Status')
                                    ->options([
                                        'Very Satisfied' => 'Very Satisfied',
                                        'Mildly Satisfied' => 'Mildly Satisfied',
                                        'Indifferent' => 'Indifferent',
                                        'Mildly disappointed' => 'Mildly disappointed',
                                        'Very disappointed' => 'Very disappointed',
                                    ])
                                    ->required()
                                    ->inlineLabel(),

                                // Relationship Status
                                Forms\Components\Select::make('relationship_status')
                                    ->label('Relationship Status')
                                    ->options([
                                        'Very Satisfied' => 'Very Satisfied',
                                        'Mildly Satisfied' => 'Mildly Satisfied',
                                        'Indifferent' => 'Indifferent',
                                        'Mildly disappointed' => 'Mildly disappointed',
                                        'Very disappointed' => 'Very disappointed',
                                    ])
                                    ->required()
                                    ->inlineLabel(),

                                // Primary Relationship Status
                                Forms\Components\Select::make('primary_relationship_status')
                                    ->label('Primary Relationship Status')
                                    ->options([
                                        'Very Satisfied' => 'Very Satisfied',
                                        'Mildly Satisfied' => 'Mildly Satisfied',
                                        'Indifferent' => 'Indifferent',
                                        'Mildly disappointed' => 'Mildly disappointed',
                                        'Very disappointed' => 'Very disappointed',
                                    ])
                                    ->required()
                                    ->inlineLabel(),

                                // Ability to Enjoy Leisure Activities
                                Forms\Components\Select::make('ability_to_enjoy_leisure_activities')
                                    ->label('Ability to Enjoy Leisure Activities')
                                    ->options([
                                        'Very Satisfied' => 'Very Satisfied',
                                        'Mildly Satisfied' => 'Mildly Satisfied',
                                        'Indifferent' => 'Indifferent',
                                        'Mildly disappointed' => 'Mildly disappointed',
                                        'Very disappointed' => 'Very disappointed',
                                    ])
                                    ->required()
                                    ->inlineLabel(),

                                // Spirituality
                                Forms\Components\Select::make('spirituality')
                                    ->label('Spirituality')
                                    ->options([
                                        'Very Satisfied' => 'Very Satisfied',
                                        'Mildly Satisfied' => 'Mildly Satisfied',
                                        'Indifferent' => 'Indifferent',
                                        'Mildly disappointed' => 'Mildly disappointed',
                                        'Very disappointed' => 'Very disappointed',
                                    ])
                                    ->required()
                                    ->inlineLabel(),

                                // Substance Use
                                Forms\Components\Select::make('substance_use')
                                    ->label('Substance Use')
                                    ->options([
                                        'Occasional' => 'Occasional',
                                        'Monthly Use' => 'Monthly Use',
                                        'Weekly Use' => 'Weekly Use',
                                        'Daily Use' => 'Daily Use',
                                        'No Use' => 'No Use',
                                    ])
                                    ->required(),

                                // Revenue
                                Forms\Components\TextInput::make('revenue')
                                    ->label('Revenue')
                                    ->numeric()
                                    ->required()
                                    ->inlineLabel(),

                                // Scheme ID
                                Forms\Components\Select::make('scheme_id')
                                    ->label('Scheme')
                                    ->options(function () {
                                        return Scheme::pluck('scheme_name', 'scheme_id');
                                    })
                                    ->required()
                                    ->inlineLabel(),

                                // Visit Date
                                Forms\Components\DatePicker::make('visit_date')
                                    ->label('Visit Date')
                                    ->required()
                                    ->inlineLabel(),
                            ]),

                        // Second Column
                        Grid::make(2)
                            ->schema([
                                // Substance Used
                                Forms\Components\Select::make('substance_used')
                                    ->label('Substance Used')
                                    ->options([
                                        'Alcohol' => 'Alcohol',
                                        'Tobacco' => 'Tobacco',
                                        'Heroin' => 'Heroin',
                                        'Cocaine' => 'Cocaine',
                                        'Cannabis' => 'Cannabis',
                                    ])
                                    ->required()
                                    ->inlineLabel(),

                                // Assessment Remarks
                                Forms\Components\Textarea::make('assessment_remarks')
                                    ->label('Assessment Remarks')
                                    ->required()
                                    ->inlineLabel(),

                                // Level of Self Esteem
                                Forms\Components\Select::make('level_of_self_esteem')
                                    ->label('Level of Self Esteem')
                                    ->options([
                                        'Very Satisfied' => 'Very Satisfied',
                                        'Mildly Satisfied' => 'Mildly Satisfied',
                                        'Indifferent' => 'Indifferent',
                                        'Mildly disappointed' => 'Mildly disappointed',
                                        'Very disappointed' => 'Very disappointed',
                                    ])
                                    ->required(),

                                // Sex Life
                                Forms\Components\Select::make('sex_life')
                                    ->label('Sex Life')
                                    ->options([
                                        'Very Satisfied' => 'Very Satisfied',
                                        'Mildly Satisfied' => 'Mildly Satisfied',
                                        'Indifferent' => 'Indifferent',
                                        'Mildly disappointed' => 'Mildly disappointed',
                                        'Very disappointed' => 'Very disappointed',
                                    ])
                                    ->required()
                                    ->inlineLabel(),

                                // Ability to Cope and Recover from Disappointments
                                Forms\Components\Select::make('ability_to_cope_recover_disappointments')
                                    ->label('Ability to Cope and Recover from Disappointments')
                                    ->options([
                                        'Very Satisfied' => 'Very Satisfied',
                                        'Mildly Satisfied' => 'Mildly Satisfied',
                                        'Indifferent' => 'Indifferent',
                                        'Mildly disappointed' => 'Mildly disappointed',
                                        'Very disappointed' => 'Very disappointed',
                                    ])
                                    ->required()
                                    ->inlineLabel(),

                                // Rate of Personal Development and Growth
                                Forms\Components\Select::make('rate_of_personal_development_growth')
                                    ->label('Rate of Personal Development and Growth')
                                    ->options([
                                        'Very Satisfied' => 'Very Satisfied',
                                        'Mildly Satisfied' => 'Mildly Satisfied',
                                        'Indifferent' => 'Indifferent',
                                        'Mildly disappointed' => 'Mildly disappointed',
                                        'Very disappointed' => 'Very disappointed',
                                    ])
                                    ->required()
                                    ->inlineLabel(),

                                // Achievement of Balance in Life
                                Forms\Components\Select::make('achievement_of_balance_in_life')
                                    ->label('Achievement of Balance in Life')
                                    ->options([
                                        'Very Satisfied' => 'Very Satisfied',
                                        'Mildly Satisfied' => 'Mildly Satisfied',
                                        'Indifferent' => 'Indifferent',
                                        'Mildly disappointed' => 'Mildly disappointed',
                                        'Very disappointed' => 'Very disappointed',
                                    ])
                                    ->required()
                                    ->inlineLabel(),

                                // Social Support System
                                Forms\Components\Select::make('social_support_system')
                                    ->label('Social Support System')
                                    ->options([
                                        'Very Satisfied' => 'Very Satisfied',
                                        'Mildly Satisfied' => 'Mildly Satisfied',
                                        'Indifferent' => 'Indifferent',
                                        'Mildly disappointed' => 'Mildly disappointed',
                                        'Very disappointed' => 'Very disappointed',
                                    ])
                                    ->required()
                                    ->inlineLabel(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Psychosocial ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->formatStateUsing(fn($state, $record) => $record->patient ? $record->patient->firstname . ' ' . $record->patient->lastname : 'N/A')
                    ->searchable(['firstname', 'lastname'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_visit')
                    ->label('Last Visit')
                    ->date()
                    ->sortable()
                    ->extraAttributes(fn ($state) => [
                        'class' => match (true) {
                            Carbon::parse($state)->isPast() => 'bg-red-100 text-red-800',
                            Carbon::parse($state)->isToday() => 'bg-yellow-100 text-yellow-800',
                            Carbon::parse($state)->isFuture() => 'bg-green-100 text-green-800',
                            default => 'bg-black-100',
                        }
                    ]),
                Tables\Columns\TextColumn::make('educational_level')
                    ->label('Educational Level')
                    ->sortable(),
                Tables\Columns\TextColumn::make('career_business')
                    ->label('Career/Business')
                    ->sortable(),
                Tables\Columns\TextColumn::make('marital_status')
                    ->label('Marital Status')
                    ->sortable(),
                Tables\Columns\TextColumn::make('relationship_status')
                    ->label('Relationship Status')
                    ->sortable(),
                Tables\Columns\TextColumn::make('primary_relationship_status')
                    ->label('Primary Relationship Status')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ability_to_enjoy_leisure_activities')
                    ->label('Leisure Activities')
                    ->sortable(),
                Tables\Columns\TextColumn::make('spirituality')
                    ->label('Spirituality')
                    ->sortable(),
                Tables\Columns\TextColumn::make('level_of_self_esteem')
                    ->label('Self-Esteem')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sex_life')
                    ->label('Sex Life')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ability_to_cope_recover_disappointments')
                    ->label('Coping Ability')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rate_of_personal_development_growth')
                    ->label('Personal Growth')
                    ->sortable(),
                Tables\Columns\TextColumn::make('achievement_of_balance_in_life')
                    ->label('Life Balance')
                    ->sortable(),
                Tables\Columns\TextColumn::make('social_support_system')
                    ->label('Social Support')
                    ->sortable(),
                Tables\Columns\TextColumn::make('substance_use')
                    ->label('Substance Use')
                    ->sortable(),
                Tables\Columns\TextColumn::make('substance_used')
                    ->label('Substances Used')
                    ->sortable(),
                Tables\Columns\TextColumn::make('assessment_remarks')
                    ->label('Remarks')
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
                Tables\Columns\TextColumn::make('scheme.scheme_name')
                    ->label('Scheme')
                    ->formatStateUsing(fn($state, $record) => $record->scheme ? $record->scheme->scheme_name : 'N/A')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('visit_date')
                    ->label('Visit Date')
                    ->date()
                    ->sortable(),
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
                            ->saveRelationshipsUsing(function ($component, $state) {
                                $component->getModelInstance()->patient_id = $state;
                            }),
                        // Last Visit
                        Forms\Components\DatePicker::make('last_visit')
                            ->label('Last Visit')
                            ->required()
                            ->inlineLabel(),

                        // Next Review
                        Forms\Components\DatePicker::make('next_review')
                            ->label('Next Review')
                            ->required()
                            ->inlineLabel(),

                        // Educational Level
                        Forms\Components\Select::make('educational_level')
                            ->label('Educational Level')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->nullable()
                            ->inlineLabel(),

                        // Career/Business
                        Forms\Components\Select::make('career_business')
                            ->label('Career/Business')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Marital Status
                        Forms\Components\Select::make('marital_status')
                            ->label('Marital Status')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Relationship Status
                        Forms\Components\Select::make('relationship_status')
                            ->label('Relationship Status')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Primary Relationship Status
                        Forms\Components\Select::make('primary_relationship_status')
                            ->label('Primary Relationship Status')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Ability to Enjoy Leisure Activities
                        Forms\Components\Select::make('ability_to_enjoy_leisure_activities')
                            ->label('Ability to Enjoy Leisure Activities')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Spirituality
                        Forms\Components\Select::make('spirituality')
                            ->label('Spirituality')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Substance Use
                        Forms\Components\Select::make('substance_use')
                            ->label('Substance Use')
                            ->options([
                                'Occasional' => 'Occasional',
                                'Monthly Use' => 'Monthly Use',
                                'Weekly Use' => 'Weekly Use',
                                'Daily Use' => 'Daily Use',
                                'No Use' => 'No Use',
                            ])
                            ->required(),

                        // Revenue
                        Forms\Components\TextInput::make('revenue')
                            ->label('Revenue')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),

                        // Scheme ID
                        Forms\Components\Select::make('scheme_id')
                            ->label('Scheme')
                            ->options(function () {
                                return Scheme::pluck('scheme_name', 'scheme_id');
                            })
                            ->required()
                            ->inlineLabel(),

                        // Visit Date
                        Forms\Components\DatePicker::make('visit_date')
                            ->label('Visit Date')
                            ->required()
                            ->inlineLabel(),

                        // Substance Used
                        Forms\Components\Select::make('substance_used')
                            ->label('Substance Used')
                            ->options([
                                'Alcohol' => 'Alcohol',
                                'Tobacco' => 'Tobacco',
                                'Heroin' => 'Heroin',
                                'Cocaine' => 'Cocaine',
                                'Cannabis' => 'Cannabis',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Assessment Remarks
                        Forms\Components\Textarea::make('assessment_remarks')
                            ->label('Assessment Remarks')
                            ->required()
                            ->inlineLabel(),

                        // Level of Self Esteem
                        Forms\Components\Select::make('level_of_self_esteem')
                            ->label('Level of Self Esteem')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required(),

                        // Sex Life
                        Forms\Components\Select::make('sex_life')
                            ->label('Sex Life')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Ability to Cope and Recover from Disappointments
                        Forms\Components\Select::make('ability_to_cope_recover_disappointments')
                            ->label('Ability to Cope and Recover from Disappointments')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Rate of Personal Development and Growth
                        Forms\Components\Select::make('rate_of_personal_development_growth')
                            ->label('Rate of Personal Development and Growth')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Achievement of Balance in Life
                        Forms\Components\Select::make('achievement_of_balance_in_life')
                            ->label('Achievement of Balance in Life')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Social Support System
                        Forms\Components\Select::make('social_support_system')
                            ->label('Social Support System')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),
                    ]))
                    ->modalWidth('lg')
                    ->slideOver()
                    ->before(function () {
                        // Debug to confirm this is a create action
                        \Log::info('CreateAction triggered in PsychosocialResource');
                    })
                    ->after(function ($record) {
                        // Debug to confirm the record was created
                        \Log::info('Record created with ID: ' . $record->id);
                    }),
                ExportAction::make()
                    ->label('Export Excel')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('psychosocial_export_' . now()->format('Y-m-d_H-i-s'))
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
                                Column::make('last_visit')
                                    ->heading('Last Visit')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : 'N/A'),
                                Column::make('next_review')
                                    ->heading('Next Review')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : 'N/A'),
                                Column::make('visit_date')
                                    ->heading('Visit Date')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : 'N/A'),
                                Column::make('educational_level')
                                    ->heading('Educational Level'),
                                Column::make('career_business')
                                    ->heading('Career/Business'),
                                Column::make('marital_status')
                                    ->heading('Marital Status'),
                                Column::make('relationship_status')
                                    ->heading('Relationship Status'),
                                Column::make('primary_relationship_status')
                                    ->heading('Primary Relationship Status'),
                                Column::make('ability_to_enjoy_leisure_activities')
                                    ->heading('Leisure Activities'),
                                Column::make('spirituality')
                                    ->heading('Spirituality'),
                                Column::make('level_of_self_esteem')
                                    ->heading('Self-Esteem'),
                                Column::make('sex_life')
                                    ->heading('Sex Life'),
                                Column::make('ability_to_cope_recover_disappointments')
                                    ->heading('Coping Ability'),
                                Column::make('rate_of_personal_development_growth')
                                    ->heading('Personal Growth'),
                                Column::make('achievement_of_balance_in_life')
                                    ->heading('Life Balance'),
                                Column::make('social_support_system')
                                    ->heading('Social Support'),
                                Column::make('substance_use')
                                    ->heading('Substance Use'),
                                Column::make('substance_used')
                                    ->heading('Substances Used'),
                                Column::make('assessment_remarks')
                                    ->heading('Remarks'),
                                Column::make('scheme.scheme_name')
                                    ->heading('Scheme'),
                                Column::make('revenue')
                                    ->heading('Revenue (KES)'),
                                Column::make('created_at')
                                    ->heading('Created At')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i') : 'N/A'),
                                Column::make('updated_at')
                                    ->heading('Updated At')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i') : 'N/A'),
                                Column::make('deleted_at')
                                    ->heading('Deleted At')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i') : 'N/A'),
                            ])
                            ->withWriterType(Excel::XLSX)
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->form(fn(Form $form) => $form->schema([
                        Forms\Components\TextInput::make('id')
                            ->label('Psychosocial ID')
                            ->disabled(),
                        Forms\Components\TextInput::make('patient.full_name')
                            ->label('Patient')
                            ->formatStateUsing(fn($state, $record) => $record->patient ? $record->patient->firstname . ' ' . $record->patient->lastname : 'N/A')
                            ->disabled(),
                        Forms\Components\TextInput::make('last_visit')
                            ->label('Last Visit')
                            ->disabled(),
                        Forms\Components\TextInput::make('next_review')
                            ->label('Next Review')
                            ->disabled(),
                        Forms\Components\TextInput::make('visit_date')
                            ->label('Visit Date')
                            ->disabled(),
                        Forms\Components\TextInput::make('created_at')
                            ->label('Created At')
                            ->disabled(),
                        Forms\Components\TextInput::make('updated_at')
                            ->label('Updated At')
                            ->disabled(),
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
                            ->saveRelationshipsUsing(function ($component, $state) {
                                $component->getModelInstance()->patient_id = $state;
                            }),
                        // Last Visit
                        Forms\Components\DatePicker::make('last_visit')
                            ->label('Last Visit')
                            ->required()
                            ->inlineLabel(),

                        // Next Review
                        Forms\Components\DatePicker::make('next_review')
                            ->label('Next Review')
                            ->required()
                            ->inlineLabel(),

                        // Educational Level
                        Forms\Components\Select::make('educational_level')
                            ->label('Educational Level')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->nullable()
                            ->inlineLabel(),

                        // Career/Business
                        Forms\Components\Select::make('career_business')
                            ->label('Career/Business')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Marital Status
                        Forms\Components\Select::make('marital_status')
                            ->label('Marital Status')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Relationship Status
                        Forms\Components\Select::make('relationship_status')
                            ->label('Relationship Status')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Primary Relationship Status
                        Forms\Components\Select::make('primary_relationship_status')
                            ->label('Primary Relationship Status')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Ability to Enjoy Leisure Activities
                        Forms\Components\Select::make('ability_to_enjoy_leisure_activities')
                            ->label('Ability to Enjoy Leisure Activities')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Spirituality
                        Forms\Components\Select::make('spirituality')
                            ->label('Spirituality')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Substance Use
                        Forms\Components\Select::make('substance_use')
                            ->label('Substance Use')
                            ->options([
                                'Occasional' => 'Occasional',
                                'Monthly Use' => 'Monthly Use',
                                'Weekly Use' => 'Weekly Use',
                                'Daily Use' => 'Daily Use',
                                'No Use' => 'No Use',
                            ])
                            ->required(),

                        // Revenue
                        Forms\Components\TextInput::make('revenue')
                            ->label('Revenue')
                            ->numeric()
                            ->required()
                            ->inlineLabel(),

                        // Scheme ID
                        Forms\Components\Select::make('scheme_id')
                            ->label('Scheme')
                            ->options(function () {
                                return Scheme::pluck('scheme_name', 'scheme_id');
                            })
                            ->required()
                            ->inlineLabel(),

                        // Visit Date
                        Forms\Components\DatePicker::make('visit_date')
                            ->label('Visit Date')
                            ->required()
                            ->inlineLabel(),

                        // Substance Used
                        Forms\Components\Select::make('substance_used')
                            ->label('Substance Used')
                            ->options([
                                'Alcohol' => 'Alcohol',
                                'Tobacco' => 'Tobacco',
                                'Heroin' => 'Heroin',
                                'Cocaine' => 'Cocaine',
                                'Cannabis' => 'Cannabis',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Assessment Remarks
                        Forms\Components\Textarea::make('assessment_remarks')
                            ->label('Assessment Remarks')
                            ->required()
                            ->inlineLabel(),

                        // Level of Self Esteem
                        Forms\Components\Select::make('level_of_self_esteem')
                            ->label('Level of Self Esteem')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required(),

                        // Sex Life
                        Forms\Components\Select::make('sex_life')
                            ->label('Sex Life')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Ability to Cope and Recover from Disappointments
                        Forms\Components\Select::make('ability_to_cope_recover_disappointments')
                            ->label('Ability to Cope and Recover from Disappointments')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Rate of Personal Development and Growth
                        Forms\Components\Select::make('rate_of_personal_development_growth')
                            ->label('Rate of Personal Development and Growth')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Achievement of Balance in Life
                        Forms\Components\Select::make('achievement_of_balance_in_life')
                            ->label('Achievement of Balance in Life')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),

                        // Social Support System
                        Forms\Components\Select::make('social_support_system')
                            ->label('Social Support System')
                            ->options([
                                'Very Satisfied' => 'Very Satisfied',
                                'Mildly Satisfied' => 'Mildly Satisfied',
                                'Indifferent' => 'Indifferent',
                                'Mildly disappointed' => 'Mildly disappointed',
                                'Very disappointed' => 'Very disappointed',
                            ])
                            ->required()
                            ->inlineLabel(),
                    ]))
                    ->modalWidth('lg')
                    ->slideOver()
                    ->before(function () {
                        // Debug to confirm this is an edit action
                        \Log::info('EditAction triggered in PsychosocialResource');
                    }),

                Tables\Actions\Action::make('restore')
                    ->label('Restore')
                    ->action(fn(Model $record) => $record->restore())
                    ->visible(fn(Model $record) => $record->trashed())
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Restore Psychosocial Assessment')
                    ->modalSubheading('Are you sure you want to restore this psychosocial assessment?'),

                Tables\Actions\Action::make('forceDelete')
                    ->label('Delete')
                    ->action(fn(Model $record) => $record->forceDelete())
                    ->visible(fn(Model $record) => $record->trashed())
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Permanently Delete Psychosocial Assessment')
                    ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),

                Tables\Actions\DeleteAction::make()
                    ->label('Deactivate')
                    ->visible(fn(Model $record) => !$record->trashed())
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate Psychosocial Assessment')
                    ->modalSubheading('The psychosocial assessment will be moved to the deactivated list. You can restore it later.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Deactivate')
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Psychosocial Assessments')
                        ->modalSubheading('The selected psychosocial assessments will be moved to the deactivated list. You can restore them later.'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Restore')
                        ->requiresConfirmation()
                        ->modalHeading('Restore Psychosocial Assessments')
                        ->modalSubheading('Are you sure you want to restore the selected psychosocial assessments?'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Delete')
                        ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Psychosocial Assessments')
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
            'index' => Pages\ListPsychosocials::route('/'),
            //'create' => Pages\CreatePsychosocial::route('/create'),
            //'edit' => Pages\EditPsychosocial::route('/{record}/edit'),
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