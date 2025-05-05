<?php

namespace App\Filament\Resources\CohortResource\Pages;

use App\Filament\Resources\CohortResource;
use App\Models\Cohort;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCohorts extends ListRecords
{
    protected static string $resource = CohortResource::class;

    public function getTabs(): array
    {
        return [
            'active' => Tab::make()
                ->label('Active Cohorts')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->whereNull('deleted_at'))
                ->icon('heroicon-o-check-circle')
                ->badge(Cohort::cachedActiveCount()),

            'trashed' => Tab::make()
                ->label('Deactivated Cohorts')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->onlyTrashed())
                ->icon('heroicon-o-x-circle')
                ->badge(Cohort::cachedTrashedCount()),

            'all' => Tab::make()
                ->label('All Cohorts')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->withTrashed())
                ->icon('heroicon-o-list-bullet')
                ->badge(Cohort::cachedTotalCount()),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->withoutGlobalScopes()
            ->orderBy('cohort_name');
    }

 
}
