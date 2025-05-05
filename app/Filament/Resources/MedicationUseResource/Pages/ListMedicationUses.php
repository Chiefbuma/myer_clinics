<?php

namespace App\Filament\Resources\MedicationUseResource\Pages;

use App\Filament\Resources\MedicationUseResource;
use App\Models\MedicationUse;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMedicationUses extends ListRecords
{
    protected static string $resource = MedicationUseResource::class;

    public function getTabs(): array
    {
        return [
            'non_trashed' => Tab::make()
                ->label('Active Medication Uses')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->whereNull('deleted_at'))
                ->icon('heroicon-o-check-circle')
                ->badge(MedicationUse::cachedActiveCount()),

            'trashed' => Tab::make()
                ->label('Deactivated Medication Uses')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->onlyTrashed())
                ->icon('heroicon-o-x-circle')
                ->badge(MedicationUse::cachedTrashedCount()),

            'all' => Tab::make()
                ->label('All Medication Uses')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->withTrashed())
                ->icon('heroicon-o-list-bullet')
                ->badge(MedicationUse::cachedTotalCount()),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->withoutGlobalScopes()
            ->orderBy('visit_date', 'desc');
    }

}