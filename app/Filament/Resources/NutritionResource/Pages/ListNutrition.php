<?php

namespace App\Filament\Resources\NutritionResource\Pages;

use App\Filament\Resources\NutritionResource;
use App\Models\Nutrition;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListNutrition extends ListRecords
{
    protected static string $resource = NutritionResource::class;

    public function getTitle(): string
    {
        return __('Nutrition Reports');
    }

    public function getTabs(): array
    {
        return [
            'non_trashed' => Tab::make()
                ->label('Active Nutrition Reports')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->whereNull('deleted_at'))
                ->icon('heroicon-o-check-circle')
                ->badge(Nutrition::cachedActiveCount()),

            'trashed' => Tab::make()
                ->label('Deactivated Nutrition Reports')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->onlyTrashed())
                ->icon('heroicon-o-x-circle')
                ->badge(Nutrition::cachedTrashedCount()),

            'all' => Tab::make()
                ->label('All Nutrition Reports')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->withTrashed())
                ->icon('heroicon-o-list-bullet')
                ->badge(Nutrition::cachedTotalCount()),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->withoutGlobalScopes()
            ->orderBy('visit_date', 'desc');
    }
}