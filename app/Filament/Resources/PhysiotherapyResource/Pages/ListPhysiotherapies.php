<?php

namespace App\Filament\Resources\PhysiotherapyResource\Pages;

use App\Filament\Resources\PhysiotherapyResource;
use App\Models\Physiotherapy;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPhysiotherapies extends ListRecords
{
    protected static string $resource = PhysiotherapyResource::class;

    public function getTabs(): array
    {
        return [
            'non_trashed' => Tab::make()
                ->label('Active Physiotherapy Reports')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->whereNull('deleted_at'))
                ->icon('heroicon-o-check-circle')
                ->badge(Physiotherapy::cachedActiveCount()),

            'trashed' => Tab::make()
                ->label('Deactivated Physiotherapy Reports')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->onlyTrashed())
                ->icon('heroicon-o-x-circle')
                ->badge(Physiotherapy::cachedTrashedCount()),

            'all' => Tab::make()
                ->label('All Physiotherapy Reports')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->withTrashed())
                ->icon('heroicon-o-list-bullet')
                ->badge(Physiotherapy::cachedTotalCount()),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->withoutGlobalScopes()
            ->orderBy('visit_date', 'desc');
    }


}