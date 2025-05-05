<?php

namespace App\Filament\Resources\SchemeResource\Pages;

use App\Filament\Resources\SchemeResource;
use App\Models\Scheme;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ListSchemes extends ListRecords
{
    protected static string $resource = SchemeResource::class;

    public function getTabs(): array
    {
        $tabs = [
            'non_trashed' => Tab::make()
                ->label('Active Schemes')
                ->modifyQueryUsing(function (Builder $query) {
                    $query = $query->withoutGlobalScopes()->whereNull('deleted_at');
                    Log::info('Active Schemes tab query', ['count' => $query->count()]);
                    return $query;
                })
                ->icon('heroicon-o-check-circle')
                ->badge(Scheme::cachedActiveCount()),

            'trashed' => Tab::make()
                ->label('Deactivated Schemes')
                ->modifyQueryUsing(function (Builder $query) {
                    $query = $query->withoutGlobalScopes()->onlyTrashed();
                    Log::info('Deactivated Schemes tab query', ['count' => $query->count()]);
                    return $query;
                })
                ->icon('heroicon-o-x-circle')
                ->badge(Scheme::cachedTrashedCount()),

            'all' => Tab::make()
                ->label('All Schemes')
                ->modifyQueryUsing(function (Builder $query) {
                    $query = $query->withoutGlobalScopes()->withTrashed();
                    Log::info('All Schemes tab query', ['count' => $query->count()]);
                    return $query;
                })
                ->icon('heroicon-o-list-bullet')
                ->badge(Scheme::cachedTotalCount()),
        ];

        Log::info('Tabs generated', ['tabs' => array_keys($tabs)]);
        return $tabs;
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery()->withoutGlobalScopes()->orderBy('scheme_name');
        Log::info('Table query executed', ['count' => $query->count()]);
        return $query;
    }


}