<?php

namespace App\Filament\Resources\PatientResource\Pages;

use App\Filament\Resources\PatientResource;
use App\Models\Patient;
use App\Models\Nutrition;
use App\Models\Chronic;
use App\Models\Physiotherapy;
use App\Models\Call;
use App\Models\CallResult;
use App\Models\Psychosocial;
use App\Models\MedicationUse;
use App\Models\Medication;
use App\Models\Procedure;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use App\Models\Scheme;
use App\Models\Specialist;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;



class ManagePatient extends Page
{
    use InteractsWithForms, InteractsWithActions;

    protected static string $resource = PatientResource::class;
    protected static string $view = 'filament.resources.patient-resource.pages.manage-patient';

    public Patient $record;

    // Nutrition Form Data
    public $scheme_id;
    public $last_visit;
    public $next_review;
    public $muscle_mass;
    public $bone_mass;
    public $weight;
    public $BMI;
    public $total_body_fat;
    public $visceral_fat;
    public $weight_remarks;
    public $physical_activity;
    public $meal_plan_set_up;
    public $nutrition_adherence;
    public $nutrition_assessment_remarks;
    public $visit_date;
    public $scheme_id_nutrition;
    public $revenue_nutrition;

    // Chronic Form Data
    public $procedure_id;
    public $speciality_id;
    public $refill_date;
    public $compliance;
    public $exercise;
    public $clinical_goals;
    public $nutrition_follow_up;
    public $psychosocial;
    public $annual_check_up;
    public $specialist_review;
    public $vitals_monitoring;
    public $vital_signs_monitor;
    public $last_visit_chronic;
    public $scheme_id_chronic;
    public $revenue_chronic;

    // Physiotherapy Assessment Form Data
    public $physio_visit_date;
    public $pain_level;
    public $mobility_score;
    public $range_of_motion;
    public $strength;
    public $balance;
    public $walking_ability;
    public $posture_assessment;
    public $exercise_type;
    public $frequency_per_week;
    public $duration_per_session;
    public $intensity;
    public $pain_level_before_exercise;
    public $pain_level_after_exercise;
    public $fatigue_level_before_exercise;
    public $fatigue_level_after_exercise;
    public $post_exercise_recovery_time;
    public $functional_independence;
    public $joint_swelling;
    public $muscle_spasms;
    public $progress;
    public $treatment;
    public $challenges;
    public $scheme_id_physiotherapy;
    public $revenue_physiotherapy;

    // Psychosocial Assessment Form Data
    public $last_visit_psychosocial;
    public $next_review_psychosocial;
    public $educational_level;
    public $career_business;
    public $marital_status;
    public $relationship_status;
    public $primary_relationship_status;
    public $ability_to_enjoy_leisure_activities;
    public $spirituality;
    public $level_of_self_esteem;
    public $sex_life;
    public $ability_to_cope_recover_disappointments;
    public $rate_of_personal_development_growth;
    public $achievement_of_balance_in_life;
    public $social_support_system;
    public $substance_use;
    public $substance_used;
    public $assessment_remarks;
    public $revenue_psychosocial;
    public $scheme_id_psychosocial;
    public $visit_date_psychosocial;

    // Calls Form Data
    public $call_results;
    public $call_date;

    // Medication Use Form Data
    public array $medication_use_records = [];
    public array $medications = []; // Add this property to store medications

    // Other properties and methods...

    public function mount(Patient $record): void
    {
        $this->record = $record; // Set the $record property

        // Fetch medication records for the current patient
        $this->medication_use_records = $this->getMedicationUseRecords();

        // Fetch all medications for the dropdown
        $this->medications = $this->getMedications();
    }

    // Fetch medication records for the current patient
    public function getMedicationUseRecords(): array
    {
        return MedicationUse::where('patient_id', $this->record->patient_id)
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->medication_use_id, // Include the record ID for updates
                    'medication_id' => $record->medication_id,
                    'days_supplied' => $record->days_supplied,
                    'no_pills_dispensed' => $record->no_pills_dispensed,
                    'frequency' => $record->frequency,
                    'medication_visit_date' => $record->medication_visit_date,
                ];
            })
            ->toArray();
    }

    // Fetch all medications for the dropdown
    public function getMedications(): array
    {
        return Medication::all()
            ->map(function ($medication) {
                return [
                    'medication_id' => $medication->medication_id,
                    'item_name' => $medication->item_name, // Display the medication name in the dropdown
                ];
            })
            ->toArray();
    }

    // Global Save Action
    protected ?string $saveAction = '';

    public function getBreadcrumb(): ?string
    {
        return __('Manage Patient');
    }

    public function getTitle(): string
    {
        return __('Manage Patient: ' . $this->record->firstname . ' ' . $this->record->lastname);
    }

    protected function getFormSchema(): array
    {
        return [
            Tabs::make('Tabs')
                ->tabs([
                    Tabs\Tab::make('Nutrition Assessment')
                        ->schema([
                            Section::make('Nutrition Assessment')
                                ->schema([

                                    Select::make('scheme_id')
                                        ->label('Scheme')
                                        ->options(function () {
                                            return Scheme::pluck('scheme_name', 'scheme_id');
                                        })
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input

                                    DatePicker::make('last_visit')
                                        ->label('Last Visit')
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    DatePicker::make('next_review')
                                        ->label('Next Review')
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('muscle_mass')
                                        ->label('Muscle Mass')
                                        ->numeric()
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('bone_mass')
                                        ->label('Bone Mass')
                                        ->numeric()
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('weight')
                                        ->label('Weight')
                                        ->numeric()
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('BMI')
                                        ->label('BMI')
                                        ->numeric()
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('total_body_fat')
                                        ->label('total_body_fat')
                                        ->numeric()
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('visceral_fat')
                                        ->label('Visceral Fat')
                                        ->numeric()
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input

                                    Textarea::make('weight_remarks')
                                        ->label('Weight Remarks')
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    Select::make('physical_activity')
                                        ->label('Physical Activity')
                                        ->options([
                                            'active' => 'Active',
                                            'moderate' => 'Moderate',
                                            'sedentary' => 'Sedentary',
                                        ])
                                        ->required(),
                                    Select::make('meal_plan_set_up')
                                        ->label('Meal Plan Set Up')
                                        ->options([
                                            'yes' => 'Yes',
                                            'no' => 'No',
                                        ])
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    Select::make('nutrition_adherence')
                                        ->label('Nutrition Adherence')
                                        ->options([
                                            'compliant' => 'Compliant',
                                            'non_compliant' => 'Non-Compliant',
                                        ])
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    Textarea::make('nutrition_assessment_remarks')
                                        ->label('Nutrition Assessment Remarks')
                                        ->required(),
                                    TextInput::make('revenue_nutrition')
                                        ->label('Revenue')
                                        ->numeric()
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    DatePicker::make('visit_date')
                                        ->label('Visit Date')
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                ])
                                ->columns(2),
                            Actions::make([
                                FormAction::make('saveNutrition')
                                    ->label('Save Nutrition')
                                    ->action('saveNutrition'),
                            ]),
                        ])
                        ->extraAttributes([
                            'style' => 'background-color: grey; color: white; padding: 1rem; font-size: 1.25rem;', // Custom styles

                        ]),
                    Tabs\Tab::make('Chronic Care')
                        ->schema([
                            Section::make('Chronic Care')
                                ->schema([
                                    Select::make('procedure_id')
                                        ->label('Procedure')
                                        ->options(function () {
                                            return Procedure::pluck('procedure_name', 'procedure_id');
                                        })
                                        ->required()
                                        ->inlineLabel(), // Added inlineLabel

                                    Select::make('speciality_id')
                                        ->label('Specialist')
                                        ->options(function () {
                                            return Specialist::pluck('specialist_name', 'specialist_id');
                                        })
                                        ->required()
                                        ->inlineLabel(), // Added inlineLabel

                                    DatePicker::make('refill_date')
                                        ->label('Refill Date')
                                        ->required()
                                        ->inlineLabel(), // Added inlineLabel

                                    Select::make('compliance')
                                        ->label('Compliance')
                                        ->options([
                                            'compliant' => 'Compliant',
                                            'non_compliant' => 'Non-Compliant',
                                        ])
                                        ->required()
                                        ->inlineLabel(), // Added inlineLabel

                                    Select::make('exercise')
                                        ->label('Exercise')
                                        ->options([
                                            'regular' => 'Regular',
                                            'irregular' => 'Irregular',
                                        ])
                                        ->required()
                                        ->inlineLabel(), // Added inlineLabel

                                    Select::make('clinical_goals')
                                        ->label('Clinical Goals')
                                        ->options([
                                            'active' => 'Active',
                                            'mildly_active' => 'Mildly Active',
                                            'inactive' => 'Inactive',
                                        ])
                                        ->required()
                                        ->inlineLabel(), // Added inlineLabel

                                    Textarea::make('nutrition_follow_up')
                                        ->label('Nutrition Follow-Up')
                                        ->required()
                                        ->inlineLabel(), // Added inlineLabel

                                    Textarea::make('psychosocial')
                                        ->label('Psychosocial')
                                        ->required()
                                        ->inlineLabel(), // Added inlineLabel

                                    DatePicker::make('annual_check_up')
                                        ->label('Annual Check-Up')
                                        ->required()
                                        ->inlineLabel(), // Added inlineLabel

                                    DatePicker::make('specialist_review')
                                        ->label('Specialist Review')
                                        ->required()
                                        ->inlineLabel(), // Added inlineLabel

                                    Select::make('vitals_monitoring')
                                        ->label('Vitals Monitoring')
                                        ->options([
                                            'stable' => 'Stable',
                                            'uncontrolled' => 'Uncontrolled',
                                        ])
                                        ->required()
                                        ->inlineLabel(), // Added inlineLabel

                                    TextInput::make('revenue_chronic')
                                        ->label('Revenue')
                                        ->numeric()
                                        ->required()
                                        ->inlineLabel(), // Added inlineLabel

                                    Select::make('vital_signs_monitor')
                                        ->label('Vital Signs Monitor')
                                        ->options([
                                            'monitored' => 'Monitored',
                                            'not_monitored' => 'Not Monitored',
                                        ])
                                        ->required()
                                        ->inlineLabel(), // Added inlineLabel

                                    DatePicker::make('last_visit_chronic')
                                        ->label('Last Visit')
                                        ->required()
                                        ->inlineLabel(), // Added inlineLabel

                                    Select::make('scheme_id')
                                        ->label('Scheme')
                                        ->options(function () {
                                            return Scheme::pluck('scheme_name', 'scheme_id');
                                        })
                                        ->required()
                                        ->inlineLabel(), // Added inlineLabel
                                ])
                                ->columns(2),
                            Actions::make([
                                FormAction::make('saveChronic')
                                    ->label('Save Chronic')
                                    ->action('saveChronic'),
                            ]),
                        ])
                        ->extraAttributes([
                            'style' => 'background-color: grey; color: white; padding: 1rem; font-size: 1.25rem;', // Custom styles

                        ]),


                    /* The above code is written in PHP and it seems to be defining a form or input
                   fields using a library or framework that provides a fluent syntax for creating
                   forms. */
                    Tabs\Tab::make('Calls')
                        ->schema([
                            Section::make('Calls')
                                ->schema([
                                    Select::make('call_results')
                                        ->label('Call results')
                                        ->options(function () {
                                            return CallResult::pluck('Call_result', 'Call_results_id');
                                        })
                                        ->required(),

                                    DatePicker::make('call_date')
                                        ->label('Call Date')
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                ])
                                ->columns(2),

                            Actions::make([
                                FormAction::make('saveCalls')
                                    ->label('Save Calls')
                                    ->action('saveCalls'),
                            ]),
                        ])
                        ->extraAttributes([
                            'style' => 'background-color: grey; color: white; padding: 1rem; font-size: 1.25rem;', // Custom styles

                        ]),
                    Tabs\Tab::make('Physiotherapy Assessment')
                        ->schema([
                            Section::make('Physiotherapy Assessment')
                                ->schema([
                                    DatePicker::make('physio_visit_date')
                                        ->label('Visit Date')
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('pain_level')
                                        ->label('Pain Level (Scale 0-10)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(10)
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('mobility_score')
                                        ->label('Mobility Score (1-5)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(5)
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('range_of_motion')
                                        ->label('Range of Motion (1-5)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(5)
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('strength')
                                        ->label('Strength (0-5)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(5)
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('balance')
                                        ->label('Balance (1-5)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(5)
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('walking_ability')
                                        ->label('Walking Ability (0-60 min)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(60)
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    Select::make('posture_assessment')
                                        ->label('Posture Assessment')
                                        ->options([
                                            'kyphosis' => 'Kyphosis',
                                            'scoliosis' => 'Scoliosis',
                                            'anterior_pelvic_tilt' => 'Anterior Pelvic Tilt',
                                            'lordosis' => 'Lordosis',
                                            'normal' => 'Normal',
                                        ])
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    Select::make('exercise_type')
                                        ->label('Exercise Type')
                                        ->options([
                                            'aerobic' => 'Aerobic',
                                            'strengthening' => 'Strengthening',
                                            'flexibility' => 'Flexibility',
                                            'balance' => 'Balance',
                                            'functional' => 'Functional',
                                        ])
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('frequency_per_week')
                                        ->label('Frequency (Per Week)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(7)
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('duration_per_session')
                                        ->label('Duration of Each Session (Minutes)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(60)
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('intensity')
                                        ->label('Intensity (1-10)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(10)
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('pain_level_before_exercise')
                                        ->label('Pain Level Before Exercise (0-10)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(10)
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('pain_level_after_exercise')
                                        ->label('Pain Level After Exercise (0-10)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(10)
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('fatigue_level_before_exercise')
                                        ->label('Fatigue Level Before Exercise (0-10)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(10)
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('fatigue_level_after_exercise')
                                        ->label('Fatigue Level After Exercise (0-10)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(10)
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('post_exercise_recovery_time')
                                        ->label('Post-Exercise Recovery Time (1-60 min)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(60)
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('functional_independence')
                                        ->label('Functional Independence (1-5)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(5)
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    Select::make('joint_swelling')
                                        ->label('Joint Swelling/Inflammation')
                                        ->options([
                                            1 => 'Yes',
                                            0 => 'No',
                                        ])
                                        ->required(),
                                    Select::make('muscle_spasms')
                                        ->label('Muscle Spasms')
                                        ->options([
                                            1 => 'Yes',
                                            0 => 'No',
                                        ])
                                        ->required(),
                                    TextInput::make('progress')
                                        ->label('Progress (0-5)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(5)
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    Textarea::make('treatment')
                                        ->label('Treatment')
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    Textarea::make('challenges')
                                        ->label('Challenges')
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    TextInput::make('revenue_physiotherapy')
                                        ->label('Revenue')
                                        ->numeric()
                                        ->required()
                                        ->inlineLabel()
                                        ->inlineLabel(), // This places the label on the side of the input
                                    Select::make('scheme_id')
                                        ->label('Scheme')
                                        ->options(function () {
                                            return Scheme::pluck('scheme_name', 'scheme_id');
                                        })
                                        ->required()
                                        ->inlineLabel(), // This places the label on the side of the input
                                ])
                                ->columns(2),
                            Actions::make([
                                FormAction::make('savePhysiotherapy')
                                    ->label('Save Physiotherapy')
                            ]),
                        ])
                        ->extraAttributes([
                            'style' => 'background-color: grey; color: white; padding: 1rem; font-size: 1.25rem;', // Custom styles

                        ]),

                    Tabs\Tab::make('Psychosocial Assessment')
                        ->schema([
                            Section::make('Psychosocial Assessment')
                                ->schema([
                                    // First Column
                                    Grid::make(2)
                                        ->schema([
                                            // Last Visit
                                            DatePicker::make('last_visit_psychosocial')
                                                ->label('Last Visit')
                                                ->required() // Make nullable
                                                ->inlineLabel(), // This places the label on the side of the input

                                            // Next Review
                                            DatePicker::make('next_review_psychosocial')
                                                ->label('Next Review')
                                                ->required() // Keep required
                                                ->inlineLabel(), // This places the label on the side of the input

                                            // Educational Level
                                            Select::make('educational_level')
                                                ->label('Educational Level')
                                                ->options([
                                                    'Very Satisfied' => 'Very Satisfied',
                                                    'Mildly Satisfied' => 'Mildly Satisfied',
                                                    'Indifferent' => 'Indifferent',
                                                    'Mildly disappointed' => 'Mildly disappointed',
                                                    'Very disappointed' => 'Very disappointed',
                                                ])
                                                ->nullable() // Make nullable
                                                ->inlineLabel(), // This places the label on the side of the input

                                            // Career/Business
                                            Select::make('career_business')
                                                ->label('Career/Business')
                                                ->options([
                                                    'Very Satisfied' => 'Very Satisfied',
                                                    'Mildly Satisfied' => 'Mildly Satisfied',
                                                    'Indifferent' => 'Indifferent',
                                                    'Mildly disappointed' => 'Mildly disappointed',
                                                    'Very disappointed' => 'Very disappointed',
                                                ])
                                                ->required() // Make nullable
                                                ->inlineLabel(), // This places the label on the side of the input

                                            // Marital Status
                                            Select::make('marital_status')
                                                ->label('Marital Status')
                                                ->options([
                                                    'Very Satisfied' => 'Very Satisfied',
                                                    'Mildly Satisfied' => 'Mildly Satisfied',
                                                    'Indifferent' => 'Indifferent',
                                                    'Mildly disappointed' => 'Mildly disappointed',
                                                    'Very disappointed' => 'Very disappointed',
                                                ])
                                                ->required()
                                                ->inlineLabel(), // This places the label on the side of the input // Make nullable

                                            // Relationship Status
                                            Select::make('relationship_status')
                                                ->label('Relationship Status')
                                                ->options([
                                                    'Very Satisfied' => 'Very Satisfied',
                                                    'Mildly Satisfied' => 'Mildly Satisfied',
                                                    'Indifferent' => 'Indifferent',
                                                    'Mildly disappointed' => 'Mildly disappointed',
                                                    'Very disappointed' => 'Very disappointed',
                                                ])
                                                ->required()
                                                ->inlineLabel(), // This places the label on the side of the input// Make nullable

                                            // Primary Relationship Status
                                            Select::make('primary_relationship_status')
                                                ->label('Primary Relationship Status')
                                                ->options([
                                                    'Very Satisfied' => 'Very Satisfied',
                                                    'Mildly Satisfied' => 'Mildly Satisfied',
                                                    'Indifferent' => 'Indifferent',
                                                    'Mildly disappointed' => 'Mildly disappointed',
                                                    'Very disappointed' => 'Very disappointed',
                                                ])
                                                ->required()
                                                ->inlineLabel(), // This places the label on the side of the input // Make nullable

                                            // Ability to Enjoy Leisure Activities
                                            Select::make('ability_to_enjoy_leisure_activities')
                                                ->label('Ability to Enjoy Leisure Activities')
                                                ->options([
                                                    'Very Satisfied' => 'Very Satisfied',
                                                    'Mildly Satisfied' => 'Mildly Satisfied',
                                                    'Indifferent' => 'Indifferent',
                                                    'Mildly disappointed' => 'Mildly disappointed',
                                                    'Very disappointed' => 'Very disappointed',
                                                ])
                                                ->required()
                                                ->inlineLabel(), // This places the label on the side of the input // Make nullable

                                            // Spirituality
                                            Select::make('spirituality')
                                                ->label('Spirituality')
                                                ->options([
                                                    'Very Satisfied' => 'Very Satisfied',
                                                    'Mildly Satisfied' => 'Mildly Satisfied',
                                                    'Indifferent' => 'Indifferent',
                                                    'Mildly disappointed' => 'Mildly disappointed',
                                                    'Very disappointed' => 'Very disappointed',
                                                ])
                                                ->required()
                                                ->inlineLabel(), // This places the label on the side of the input// Make nullable

                                            // Substance Use
                                            Select::make('substance_use')
                                                ->label('Substance Use')
                                                ->options([
                                                    'Occasional' => 'Occasional',
                                                    'Monthly Use' => 'Monthly Use',
                                                    'Weekly Use' => 'Weekly Use',
                                                    'Daily Use' => 'Daily Use',
                                                    'No Use' => 'No Use',
                                                ])
                                                ->required(), // Make nullable

                                            // Revenue
                                            TextInput::make('revenue_psychosocial')
                                                ->label('Revenue')
                                                ->numeric()
                                                ->required()
                                                ->inlineLabel(), // This places the label on the side of the input // Make nullable

                                            // Scheme ID

                                            Select::make('scheme_id')
                                                ->label('Scheme')
                                                ->options(function () {
                                                    return Scheme::pluck('scheme_name', 'scheme_id');
                                                })
                                                ->required()
                                                ->inlineLabel(), // This places the label on the side of the input



                                            // Visit Date
                                            DatePicker::make('visit_date_psychosocial')
                                                ->label('Visit Date')
                                                ->required()
                                                ->inlineLabel(), // This places the label on the side of the input // Make nullable
                                        ]),

                                    // Second Column
                                    Grid::make(2)
                                        ->schema([
                                            // Substance Used
                                            Select::make('substance_used')
                                                ->label('Substance Used')
                                                ->options([
                                                    'Alcohol' => 'Alcohol',
                                                    'Tobacco' => 'Tobacco',
                                                    'Heroin' => 'Heroin',
                                                    'Cocaine' => 'Cocaine',
                                                    'Cannabis' => 'Cannabis',
                                                ])
                                                ->required()
                                                ->inlineLabel(), // This places the label on the side of the input // Make nullable

                                            // Assessment Remarks
                                            Textarea::make('assessment_remarks')
                                                ->label('Assessment Remarks')
                                                ->required()
                                                ->inlineLabel(), // This places the label on the side of the input // Make nullable

                                            // Level of Self Esteem
                                            Select::make('level_of_self_esteem')
                                                ->label('Level of Self Esteem')
                                                ->options([
                                                    'Very Satisfied' => 'Very Satisfied',
                                                    'Mildly Satisfied' => 'Mildly Satisfied',
                                                    'Indifferent' => 'Indifferent',
                                                    'Mildly disappointed' => 'Mildly disappointed',
                                                    'Very disappointed' => 'Very disappointed',
                                                ])
                                                ->required(), // Make nullable

                                            // Sex Life
                                            Select::make('sex_life')
                                                ->label('Sex Life')
                                                ->options([
                                                    'Very Satisfied' => 'Very Satisfied',
                                                    'Mildly Satisfied' => 'Mildly Satisfied',
                                                    'Indifferent' => 'Indifferent',
                                                    'Mildly disappointed' => 'Mildly disappointed',
                                                    'Very disappointed' => 'Very disappointed',
                                                ])
                                                ->required()
                                                ->inlineLabel(), // This places the label on the side of the input // Make nullable

                                            // Ability to Cope and Recover from Disappointments
                                            Select::make('ability_to_cope_recover_disappointments')
                                                ->label('Ability to Cope and Recover from Disappointments')
                                                ->options([
                                                    'Very Satisfied' => 'Very Satisfied',
                                                    'Mildly Satisfied' => 'Mildly Satisfied',
                                                    'Indifferent' => 'Indifferent',
                                                    'Mildly disappointed' => 'Mildly disappointed',
                                                    'Very disappointed' => 'Very disappointed',
                                                ])
                                                ->required()
                                                ->inlineLabel(), // This places the label on the side of the input,
                                            // Make nullable

                                            // Rate of Personal Development and Growth
                                            Select::make('rate_of_personal_development_growth')
                                                ->label('Rate of Personal Development and Growth')
                                                ->options([
                                                    'Very Satisfied' => 'Very Satisfied',
                                                    'Mildly Satisfied' => 'Mildly Satisfied',
                                                    'Indifferent' => 'Indifferent',
                                                    'Mildly disappointed' => 'Mildly disappointed',
                                                    'Very disappointed' => 'Very disappointed',
                                                ])
                                                ->required()
                                                ->inlineLabel(), // This places the label on the side of the input // Make nullable

                                            // Achievement of Balance in Life
                                            Select::make('achievement_of_balance_in_life')
                                                ->label('Achievement of Balance in Life')
                                                ->options([
                                                    'Very Satisfied' => 'Very Satisfied',
                                                    'Mildly Satisfied' => 'Mildly Satisfied',
                                                    'Indifferent' => 'Indifferent',
                                                    'Mildly disappointed' => 'Mildly disappointed',
                                                    'Very disappointed' => 'Very disappointed',
                                                ])
                                                ->required()
                                                ->inlineLabel(), // This places the label on the side of the input// Make nullable

                                            // Social Support System
                                            Select::make('social_support_system')
                                                ->label('Social Support System')
                                                ->options([
                                                    'Very Satisfied' => 'Very Satisfied',
                                                    'Mildly Satisfied' => 'Mildly Satisfied',
                                                    'Indifferent' => 'Indifferent',
                                                    'Mildly disappointed' => 'Mildly disappointed',
                                                    'Very disappointed' => 'Very disappointed',
                                                ])
                                                ->required()
                                                ->inlineLabel(), // This places the label on the side of the input // Make nullable
                                        ]),
                                ]),


                            Actions::make([
                                FormAction::make('savePsychosocial')
                                    ->label('Save Psychosocial')
                                    ->action('savePsychosocial'),
                            ]),
                        ])
                        ->extraAttributes([
                            'style' => 'background-color: grey; color: white; padding: 1rem; font-size: 1.25rem;', // Custom styles

                        ]),

                    // In your getFormSchema() method, update the Medication Use tab:
                    Tabs\Tab::make('Medication Use')
                        ->schema([
                            Section::make('Medication Use')
                                ->schema([
                                    Repeater::make('medication_use_records')
                                        ->label('Medication Records')
                                        ->schema([
                                            Select::make('medication_id')
                                                ->label('Medication')
                                                ->options(
                                                    collect($this->medications)
                                                        ->pluck('item_name', 'medication_id')
                                                        ->toArray()
                                                )
                                                ->required(),
                                            TextInput::make('days_supplied')
                                                ->label('Days Supplied')
                                                ->numeric()
                                                ->required(),
                                            TextInput::make('no_pills_dispensed')
                                                ->label('No. of Pills Dispensed')
                                                ->numeric()
                                                ->required(),
                                            Select::make('frequency')
                                                ->label('Frequency')
                                                ->options([
                                                    'daily' => 'Daily',
                                                    'weekly' => 'Weekly',
                                                    'monthly' => 'Monthly',
                                                ])
                                                ->required(),
                                            DatePicker::make('medication_visit_date')
                                                ->label('Visit Date')
                                                ->required(),
                                        ])
                                        ->columns(5)
                                        ->default($this->medication_use_records) // Populate with existing records
                                        ->createItemButtonLabel('Add Medication Record')
                                        ->collapsible()
                                        ->cloneable()
                                        ->itemLabel(fn(array $state): ?string => $this->getMedicationName($state['medication_id'] ?? null) . ' - ' . ($state['frequency'] ?? '')),
                                ]),
                            Actions::make([
                                FormAction::make('saveMedicationUse')
                                    ->label('Save Medication Use')
                                    ->action('saveMedicationUse'),
                            ]),
                        ])
                        ->extraAttributes([
                            'style' => 'background-color: grey; color: white; padding: 1rem; font-size: 1.25rem;',



                        ]),
                ]),
        ];
    }

    protected function getMedicationName($medicationId): string
    {
        if (!$medicationId) return 'New Medication';

        $medication = collect($this->medications)
            ->firstWhere('medication_id', $medicationId);

        return $medication['item_name'] ?? 'Unknown Medication';
    }


    // Save All Data (Global Save)
    public function saveAll(): void
    {
        $this->saveNutrition();
        $this->saveChronic();
        $this->savePhysiotherapy();
        $this->savePsychosocial();
        $this->saveCalls();
    }

    // Save Nutrition Data
    public function saveNutrition(): void
    {
        $this->validate([
            'scheme_id' => 'required',
            'last_visit' => 'required',
            'next_review' => 'required',
            'muscle_mass' => 'required',
            'bone_mass' => 'required',
            'weight' => 'required',
            'BMI' => 'required',
            'total_body_fat' => 'required',
            'visceral_fat' => 'required',
            'weight_remarks' => 'required',
            'physical_activity' => 'required',
            'meal_plan_set_up' => 'required',
            'nutrition_adherence' => 'required',
            'nutrition_assessment_remarks' => 'required',
            'revenue_nutrition' => 'required|numeric',
            'visit_date' => 'required',
        ]);

        DB::transaction(function () {
            Nutrition::create([
                'patient_id' => $this->record->patient_id,
                'scheme_id' => $this->scheme_id,
                'last_visit' => $this->last_visit,
                'next_review' => $this->next_review,
                'muscle_mass' => $this->muscle_mass,
                'bone_mass' => $this->bone_mass,
                'weight' => $this->weight,
                'BMI' => $this->BMI,
                'total_body_fat' => $this->total_body_fat,
                'visceral_fat' => $this->visceral_fat,
                'weight_remarks' => $this->weight_remarks,
                'physical_activity' => $this->physical_activity,
                'meal_plan_set_up' => $this->meal_plan_set_up,
                'nutrition_adherence' => $this->nutrition_adherence,
                'nutrition_assessment_remarks' => $this->nutrition_assessment_remarks,
                'revenue' => $this->revenue_nutrition,
                'visit_date' => $this->visit_date,
            ]);
        });

        Notification::make()
            ->title('Nutrition Data saved successfully')
            ->success()
            ->send();

        $this->resetNutritionForm();
    }

    public function saveChronic(): void
    {
        $this->validate([
            'procedure_id' => 'required|numeric',
            'speciality_id' => 'required|numeric',
            'refill_date' => 'required|date',
            'compliance' => 'required|string',
            'exercise' => 'required|string',
            'clinical_goals' => 'required|string',
            'nutrition_follow_up' => 'required|string',
            'psychosocial' => 'required|string',
            'annual_check_up' => 'required|date',
            'specialist_review' => 'required|date',
            'vitals_monitoring' => 'required|string',
            'vital_signs_monitor' => 'required|string',
            'last_visit_chronic' => 'required|date',
            'revenue_chronic' => 'required|numeric',
            'scheme_id' => 'required|numeric',
        ]);

        DB::transaction(function () {
            Chronic::create([
                'patient_id' => $this->record->patient_id,
                'procedure_id' => $this->procedure_id,
                'speciality_id' => $this->speciality_id,
                'refill_date' => $this->refill_date,
                'compliance' => $this->compliance,
                'exercise' => $this->exercise,
                'clinical_goals' => $this->clinical_goals,
                'nutrition_follow_up' => $this->nutrition_follow_up,
                'psychosocial' => $this->psychosocial,
                'annual_check_up' => $this->annual_check_up,
                'specialist_review' => $this->specialist_review,
                'vitals_monitoring' => $this->vitals_monitoring,
                'vital_signs_monitor' => $this->vital_signs_monitor,
                'last_visit' => $this->last_visit_chronic,
                'revenue' => $this->revenue_chronic,
                'scheme_id' => $this->scheme_id,
            ]);
        });

        Notification::make()
            ->title('Chronic Data saved successfully')
            ->success()
            ->send();

        $this->resetChronicForm();
    }

    // Update your saveMedicationUse method:
    public function saveMedicationUse(): void
    {
        $this->validate([
            'medication_use_records' => 'required|array',
            'medication_use_records.*.medication_id' => 'required|exists:medication,medication_id',
            'medication_use_records.*.days_supplied' => 'required|numeric|min:1',
            'medication_use_records.*.no_pills_dispensed' => 'required|numeric|min:1',
            'medication_use_records.*.frequency' => 'required|in:daily,weekly,monthly',
            'medication_use_records.*.medication_visit_date' => 'required|date',
        ]);

        DB::transaction(function () {
            // First delete any existing records not in the current submission
            $existingIds = collect($this->medication_use_records)
                ->filter(fn($record) => isset($record['id']))
                ->pluck('id')
                ->toArray();

            MedicationUse::where('patient_id', $this->record->patient_id)
                ->whereNotIn('medication_use_id', $existingIds)
                ->delete();

            // Create/update records
            foreach ($this->medication_use_records as $record) {
                $data = [
                    'patient_id' => $this->record->patient_id,
                    'medication_id' => $record['medication_id'],
                    'days_supplied' => $record['days_supplied'],
                    'no_pills_dispensed' => $record['no_pills_dispensed'],
                    'frequency' => $record['frequency'],
                    'visit_date' => $record['medication_visit_date'],
                ];

                if (isset($record['id'])) {
                    MedicationUse::where('medication_use_id', $record['id'])
                        ->update($data);
                } else {
                    MedicationUse::create($data);
                }
            }
        });

        Notification::make()
            ->title('Medication Use saved successfully')
            ->success()
            ->send();

        // Refresh the form data
        $this->medication_use_records = $this->getMedicationUseRecords();
    }

    // Save Physiotherapy Data
    public function savePhysiotherapy(): void
    {
        $this->validate([
            'physio_visit_date' => 'required|date',
            'pain_level' => 'required|numeric|min:0|max:10',
            'mobility_score' => 'required|numeric|min:1|max:5',
            'range_of_motion' => 'required|numeric|min:1|max:5',
            'strength' => 'required|numeric|min:0|max:5',
            'balance' => 'required|numeric|min:1|max:5',
            'walking_ability' => 'required|numeric|min:0|max:60',
            'posture_assessment' => 'required',
            'exercise_type' => 'required',
            'frequency_per_week' => 'required|numeric|min:1|max:7',
            'duration_per_session' => 'required|numeric|min:1|max:60',
            'intensity' => 'required|numeric|min:1|max:10',
            'pain_level_before_exercise' => 'required|numeric|min:0|max:10',
            'pain_level_after_exercise' => 'required|numeric|min:0|max:10',
            'fatigue_level_before_exercise' => 'required|numeric|min:0|max:10',
            'fatigue_level_after_exercise' => 'required|numeric|min:0|max:10',
            'post_exercise_recovery_time' => 'required|numeric|min:1|max:60',
            'functional_independence' => 'required|numeric|min:1|max:5',
            'joint_swelling' => 'required|numeric|in:0,1',
            'muscle_spasms' => 'required|numeric|in:0,1',
            'progress' => 'required|numeric|min:0|max:5',
            'treatment' => 'required',
            'challenges' => 'required',
            'revenue_physiotherapy' => 'required|numeric',
            'scheme_id_physiotherapy' => 'required|numeric',
        ]);

        DB::transaction(function () {
            Physiotherapy::create([
                'patient_id' => $this->record->patient_id,
                'visit_date' => $this->physio_visit_date,
                'pain_level' => $this->pain_level,
                'mobility_score' => $this->mobility_score,
                'range_of_motion' => $this->range_of_motion,
                'strength' => $this->strength,
                'balance' => $this->balance,
                'walking_ability' => $this->walking_ability,
                'posture_assessment' => $this->posture_assessment,
                'exercise_type' => $this->exercise_type,
                'frequency_per_week' => $this->frequency_per_week,
                'duration_per_session' => $this->duration_per_session,
                'intensity' => $this->intensity,
                'pain_level_before_exercise' => $this->pain_level_before_exercise,
                'pain_level_after_exercise' => $this->pain_level_after_exercise,
                'fatigue_level_before_exercise' => $this->fatigue_level_before_exercise,
                'fatigue_level_after_exercise' => $this->fatigue_level_after_exercise,
                'post_exercise_recovery_time' => $this->post_exercise_recovery_time,
                'functional_independence' => $this->functional_independence,
                'joint_swelling' => $this->joint_swelling,
                'muscle_spasms' => $this->muscle_spasms,
                'progress' => $this->progress,
                'treatment' => $this->treatment,
                'challenges' => $this->challenges,
                'revenue' => $this->revenue_physiotherapy,
                'scheme_id' => $this->scheme_id_physiotherapy,
            ]);
        });

        Notification::make()
            ->title('Physiotherapy Data saved successfully')
            ->success()
            ->send();

        $this->resetPhysiotherapyForm();
    }

    public function savePsychosocial(): void
    {
        $this->validate([
            'last_visit_psychosocial' => 'nullable|date',
            'next_review_psychosocial' => 'required|date',
            'educational_level' => 'nullable',
            'career_business' => 'nullable',
            'marital_status' => 'nullable',
            'relationship_status' => 'nullable',
            'primary_relationship_status' => 'nullable',
            'ability_to_enjoy_leisure_activities' => 'nullable',
            'spirituality' => 'nullable',
            'level_of_self_esteem' => 'nullable',
            'sex_life' => 'nullable',
            'ability_to_cope_recover_disappointments' => 'nullable',
            'rate_of_personal_development_growth' => 'nullable',
            'achievement_of_balance_in_life' => 'nullable',
            'social_support_system' => 'nullable',
            'substance_use' => 'nullable',
            'substance_used' => 'nullable',
            'assessment_remarks' => 'nullable',
            'revenue_psychosocial' => 'nullable|numeric',
            'scheme_id_psychosocial' => 'nullable',
            'visit_date_psychosocial' => 'nullable|date',
        ]);

        DB::transaction(function () {
            Psychosocial::create([
                'patient_id' => $this->record->patient_id,
                'last_visit' => $this->last_visit_psychosocial,
                'next_review' => $this->next_review_psychosocial,
                'educational_level' => $this->educational_level,
                'career_business' => $this->career_business,
                'marital_status' => $this->marital_status,
                'relationship_status' => $this->relationship_status,
                'primary_relationship_status' => $this->primary_relationship_status,
                'ability_to_enjoy_leisure_activities' => $this->ability_to_enjoy_leisure_activities,
                'spirituality' => $this->spirituality,
                'level_of_self_esteem' => $this->level_of_self_esteem,
                'sex_life' => $this->sex_life,
                'ability_to_cope_recover_disappointments' => $this->ability_to_cope_recover_disappointments,
                'rate_of_personal_development_growth' => $this->rate_of_personal_development_growth,
                'achievement_of_balance_in_life' => $this->achievement_of_balance_in_life,
                'social_support_system' => $this->social_support_system,
                'substance_use' => $this->substance_use,
                'substance_used' => $this->substance_used,
                'assessment_remarks' => $this->assessment_remarks,
                'revenue' => $this->revenue_psychosocial,
                'scheme_id' => $this->scheme_id_psychosocial,
                'visit_date' => $this->visit_date_psychosocial,
            ]);
        });

        Notification::make()
            ->title('Psychosocial Data saved successfully')
            ->success()
            ->send();

        $this->resetPsychosocialForm();
    }


    // Save Calls Data
    public function saveCalls(): void
    {
        $this->validate([
            'call_results' => 'required',
            'call_date' => 'required|date',
        ]);

        DB::transaction(function () {
            Call::create([
                'patient_id' => $this->record->patient_id,
                'call_results' => $this->call_results,
                'call_date' => $this->call_date,
            ]);
        });

        Notification::make()
            ->title('Calls Data saved successfully')
            ->success()
            ->send();

        $this->resetCallsForm();
    }

    // Reset Nutrition Form
    public function resetNutritionForm(): void
    {
        $this->scheme_id = null;
        $this->last_visit = null;
        $this->next_review = null;
        $this->muscle_mass = null;
        $this->bone_mass = null;
        $this->weight = null;
        $this->BMI = null;
        $this->total_body_fat = null;
        $this->visceral_fat = null;
        $this->weight_remarks = null;
        $this->physical_activity = null;
        $this->meal_plan_set_up = null;
        $this->nutrition_adherence = null;
        $this->nutrition_assessment_remarks = null;
        $this->revenue_nutrition = null;
        $this->visit_date = null;
    }

    public function resetChronicForm(): void
    {
        $this->procedure_id = null;
        $this->speciality_id = null;
        $this->refill_date = null;
        $this->compliance = null;
        $this->exercise = null;
        $this->clinical_goals = null;
        $this->nutrition_follow_up = null;
        $this->psychosocial = null;
        $this->annual_check_up = null;
        $this->specialist_review = null;
        $this->vitals_monitoring = null;
        $this->vital_signs_monitor = null;
        $this->last_visit_chronic = null;
        $this->revenue_chronic = null;
        $this->scheme_id = null;
    }

    public function resetPsychosocialForm(): void
    {
        $this->last_visit_psychosocial = null;
        $this->next_review_psychosocial = null;
        $this->educational_level = null;
        $this->career_business = null;
        $this->marital_status = null;
        $this->relationship_status = null;
        $this->primary_relationship_status = null;
        $this->ability_to_enjoy_leisure_activities = null;
        $this->spirituality = null;
        $this->level_of_self_esteem = null;
        $this->sex_life = null;
        $this->ability_to_cope_recover_disappointments = null;
        $this->rate_of_personal_development_growth = null;
        $this->achievement_of_balance_in_life = null;
        $this->social_support_system = null;
        $this->substance_use = null;
        $this->substance_used = null;
        $this->assessment_remarks = null;
        $this->revenue_psychosocial = null;
        $this->scheme_id_psychosocial = null;
        $this->visit_date_psychosocial = null;
    }

    // Reset Physiotherapy Form
    public function resetPhysiotherapyForm(): void
    {
        $this->physio_visit_date = null;
        $this->pain_level = null;
        $this->mobility_score = null;
        $this->range_of_motion = null;
        $this->strength = null;
        $this->balance = null;
        $this->walking_ability = null;
        $this->posture_assessment = null;
        $this->exercise_type = null;
        $this->frequency_per_week = null;
        $this->duration_per_session = null;
        $this->intensity = null;
        $this->pain_level_before_exercise = null;
        $this->pain_level_after_exercise = null;
        $this->fatigue_level_before_exercise = null;
        $this->fatigue_level_after_exercise = null;
        $this->post_exercise_recovery_time = null;
        $this->functional_independence = null;
        $this->joint_swelling = null;
        $this->muscle_spasms = null;
        $this->progress = null;
        $this->treatment = null;
        $this->challenges = null;
        $this->revenue_physiotherapy = null;
        $this->scheme_id_physiotherapy = null;
    }

    // Reset Calls Form
    public function resetCallsForm(): void
    {
        $this->call_results = null;
        $this->call_date = null;
    }

    // Reset Medication Use Form
    public function resetMedicationUseForm(): void
    {
        $this->medication_use_records = $this->getMedicationUseRecords(); // Refresh the form data
    }
}
