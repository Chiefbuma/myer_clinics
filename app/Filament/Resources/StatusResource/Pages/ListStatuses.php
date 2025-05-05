<?php

namespace App\Filament\Resources\StatusResource\Pages;

use App\Filament\Resources\StatusResource;
use App\Models\Status;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListStatuses extends ListRecords
{
    protected static string $resource = StatusResource::class;

    public function getTabs(): array
    {
        return [
            'active' => Tab::make()
                ->label('Active Status')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->whereNull('deleted_at'))
                ->icon('heroicon-o-check-circle')
                ->badge(Status::cachedActiveCount()),

            'trashed' => Tab::make()
                ->label('Deactivated Statuses')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->onlyTrashed())
                ->icon('heroicon-o-x-circle')
                ->badge(Status::cachedTrashedCount()),

            'all' => Tab::make()
                ->label('All Statuses')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->withTrashed())
                ->icon('heroicon-o-list-bullet')
                ->badge(Status::cachedTotalCount()),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->withoutGlobalScopes()
            ->orderBy('status');
    }
}