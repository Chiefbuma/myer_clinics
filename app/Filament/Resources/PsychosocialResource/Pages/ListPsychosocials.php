<?php

namespace App\Filament\Resources\PsychosocialResource\Pages;

use App\Filament\Resources\PsychosocialResource;
use App\Models\Psychosocial;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPsychosocials extends ListRecords
{
    protected static string $resource = PsychosocialResource::class;

    public function getTabs(): array
    {
        return [
            'non_trashed' => Tab::make()
                ->label('Active Psychosocial Assessments')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->whereNull('deleted_at'))
                ->icon('heroicon-o-check-circle')
                ->badge(Psychosocial::cachedActiveCount()),

            'trashed' => Tab::make()
                ->label('Deactivated Psychosocial Assessments')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->onlyTrashed())
                ->icon('heroicon-o-x-circle')
                ->badge(Psychosocial::cachedTrashedCount()),

            'all' => Tab::make()
                ->label('All Psychosocial Assessments')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->withTrashed())
                ->icon('heroicon-o-list-bullet')
                ->badge(Psychosocial::cachedTotalCount()),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->withoutGlobalScopes()
            ->orderBy('created_at', 'desc');
    }


}