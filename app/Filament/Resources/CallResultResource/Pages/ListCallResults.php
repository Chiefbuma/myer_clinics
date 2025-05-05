<?php

namespace App\Filament\Resources\CallResultResource\Pages;

use App\Filament\Resources\CallResultResource;
use App\Models\CallResult;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCallResults extends ListRecords
{
    protected static string $resource = CallResultResource::class;

    public function getTabs(): array
    {
        return [
            'non_trashed' => Tab::make()
                ->label('Active Call Results')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->whereNull('deleted_at'))
                ->icon('heroicon-o-check-circle')
                ->badge(CallResult::cachedActiveCount()),

            'trashed' => Tab::make()
                ->label('Deactivated Call Results')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->onlyTrashed())
                ->icon('heroicon-o-x-circle')
                ->badge(CallResult::cachedTrashedCount()),

            'all' => Tab::make()
                ->label('All Call Results')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->withTrashed())
                ->icon('heroicon-o-list-bullet')
                ->badge(CallResult::cachedTotalCount()),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->withoutGlobalScopes()
            ->orderBy('Call_result');
    }


}