<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CallResource\Pages;
use App\Models\Call;
use App\Models\CallResult;
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

class CallResource extends Resource
{
    protected static ?string $model = Call::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone';

    protected static ?string $navigationGroup = 'Assesments';
    protected static ?string $navigationGroupIcon = 'heroicon-o-chart-bar';

    public static function getModelLabel(): string
    {
        return __('Call');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Calls');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Call Details')
                    ->schema([
                        Forms\Components\Select::make('patient_id')
                            ->relationship('patient', 'patient_no')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->firstname} {$record->lastname} (ID: {$record->patient_no})")
                            ->searchable(['firstname', 'lastname', 'patient_no'])
                            ->required(),
                        Forms\Components\Select::make('call_result')
                            ->label('Call Result')
                            ->translateLabel()
                            ->options(CallResult::pluck('call_result', 'call_result'))
                            ->required(),
                        Forms\Components\DatePicker::make('call_date')
                            ->label('Call Date')
                            ->translateLabel()
                            ->required()
                            ->default(now()),
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
                Tables\Columns\TextColumn::make('call_id')
                    ->label('Call ID')
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
                Tables\Columns\TextColumn::make('call_result')
                    ->label('Call Result')
                    ->translateLabel()
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) <= 50 ? null : $state;
                    }),
                Tables\Columns\TextColumn::make('call_date')
                    ->label('Call Date')
                    ->translateLabel()
                    ->dateTime()
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
                Tables\Filters\Filter::make('call_date')
                    ->translateLabel()
                    ->form([
                        Forms\Components\DatePicker::make('from_date')
                            ->translateLabel(),
                        Forms\Components\DatePicker::make('to_date')
                            ->default(now())
                            ->translateLabel(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('call_date', '>=', $date),
                            )
                            ->when(
                                $data['to_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('call_date', '<=', $date),
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
                            ->required(),
                        Forms\Components\Select::make('call_result')
                            ->label('Call Result')
                            ->translateLabel()
                            ->options(CallResult::pluck('call_result', 'call_result'))
                            ->required(),
                        Forms\Components\DatePicker::make('call_date')
                            ->label('Call Date')
                            ->translateLabel()
                            ->required()
                            ->default(now()),
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
                            ->withFilename('calls_export_' . now()->format('Y-m-d_H-i-s'))
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
                                Column::make('call_result')
                                    ->heading('Call Result'),
                                Column::make('call_date')
                                    ->heading('Call Date')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i') : 'N/A'),
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
                        Forms\Components\TextInput::make('call_result')
                            ->label('Call Result')
                            ->translateLabel()
                            ->disabled(),
                        Forms\Components\TextInput::make('call_date')
                            ->label('Call Date')
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
                    ->modalWidth('lg')
                    ->slideOver(),
                Tables\Actions\EditAction::make()
                    ->form(fn(Form $form) => $form->schema([
                        Forms\Components\Select::make('patient_id')
                            ->relationship('patient', 'patient_no')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->firstname} {$record->lastname} (ID: {$record->patient_no})")
                            ->searchable(['firstname', 'lastname', 'patient_no'])
                            ->required(),
                        Forms\Components\Select::make('call_result')
                            ->label('Call Result')
                            ->translateLabel()
                            ->options(CallResult::pluck('call_result', 'call_result'))
                            ->required(),
                        Forms\Components\DatePicker::make('call_date')
                            ->label('Call Date')
                            ->translateLabel()
                            ->required(),
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
                    ->modalHeading('Restore Call')
                    ->modalSubheading('Are you sure you want to restore this call?'),
                Tables\Actions\Action::make('forceDelete')
                    ->label('Delete')
                    ->action(fn(Model $record) => $record->forceDelete())
                    ->visible(fn(Model $record) => $record->trashed())
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Permanently Delete Call')
                    ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),
                Tables\Actions\DeleteAction::make()
                    ->label('Deactivate')
                    ->visible(fn(Model $record) => !$record->trashed())
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate Call')
                    ->modalSubheading('The call will be moved to the deactivated list. You can restore it later.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Deactivate')
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Calls')
                        ->modalSubheading('The selected calls will be moved to the deactivated list. You can restore them later.'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Restore')
                        ->requiresConfirmation()
                        ->modalHeading('Restore Calls')
                        ->modalSubheading('Are you sure you want to restore the selected calls?'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Delete')
                        ->requiresConfirmation()
                        ->modalHeading('Permanently Delete Calls')
                        ->modalSubheading('This cannot be undone. All related data will be permanently deleted.'),
                ]),
            ])
            ->defaultSort('call_date', 'desc')
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
            'index' => Pages\ListCalls::route('/'),
            // 'create' => Pages\CreateCall::route('/create'),
            // 'view' => Pages\ViewCall::route('/{record}'),
            // 'edit' => Pages\EditCall::route('/{record}/edit'),
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
            ]);
    }
}