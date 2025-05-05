<?php

namespace App\Filament\Resources\RouteResource\Pages;

use App\Filament\Resources\RouteResource;
use App\Models\Route;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRoutes extends ListRecords
{
    protected static string $resource = RouteResource::class;

    public function getTabs(): array
    {
        return [
            'non_trashed' => Tab::make()
                ->label('Active Routes')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->whereNull('deleted_at'))
                ->icon('heroicon-o-check-circle')
                ->badge(Route::cachedActiveCount()),

            'trashed' => Tab::make()
                ->label('Deactivated Routes')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->onlyTrashed())
                ->icon('heroicon-o-x-circle')
                ->badge(Route::cachedTrashedCount()),

            'all' => Tab::make()
                ->label('All Routes')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->withTrashed())
                ->icon('heroicon-o-list-bullet')
                ->badge(Route::cachedTotalCount()),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->withoutGlobalScopes()
            ->orderBy('route_name');
    }
}