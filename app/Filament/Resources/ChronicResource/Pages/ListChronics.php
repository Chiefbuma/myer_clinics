<?php

namespace App\Filament\Resources\ChronicResource\Pages;

use App\Filament\Resources\ChronicResource;
use App\Models\Chronic;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListChronics extends ListRecords
{
    protected static string $resource = ChronicResource::class;

    public function getTabs(): array
    {
        return [
            'non_trashed' => Tab::make()
                ->label('Active Chronic Care')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->whereNull('deleted_at'))
                ->icon('heroicon-o-check-circle')
                ->badge(Chronic::cachedActiveCount()),

            'trashed' => Tab::make()
                ->label('Deactivated Chronic Care')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->onlyTrashed())
                ->icon('heroicon-o-x-circle')
                ->badge(Chronic::cachedTrashedCount()),

            'all' => Tab::make()
                ->label('All Chronic Care')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->withTrashed())
                ->icon('heroicon-o-list-bullet')
                ->badge(Chronic::cachedTotalCount()),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->withoutGlobalScopes()
            ->orderBy('last_visit', 'desc');
    }


}