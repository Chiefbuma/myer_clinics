<?php

namespace App\Filament\Resources\PatientResource\Pages;

use App\Filament\Resources\PatientResource;
use App\Models\Patient;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPatients extends ListRecords
{
    protected static string $resource = PatientResource::class;

    public function getTabs(): array
    {
        return [
            'non_trashed' => Tab::make()
                ->label('Active Patients')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->whereNull('deleted_at'))
                ->icon('heroicon-o-check-circle')
                ->badge(Patient::cachedActiveCount()),

            'trashed' => Tab::make()
                ->label('Deactivated Patients')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->onlyTrashed())
                ->icon('heroicon-o-x-circle')
                ->badge(Patient::cachedTrashedCount()),

            'all' => Tab::make()
                ->label('All Patients')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->withTrashed())
                ->icon('heroicon-o-list-bullet')
                ->badge(Patient::cachedTotalCount()),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->withoutGlobalScopes()
            ->orderBy('patient_no', 'desc');
    }


}