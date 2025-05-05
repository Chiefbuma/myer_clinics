<?php

namespace App\Filament\Resources\DiagnosisResource\Pages;

use App\Filament\Resources\DiagnosisResource;
use App\Models\Diagnosis;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDiagnoses extends ListRecords
{
    protected static string $resource = DiagnosisResource::class;

 

    public function getTabs(): array
    {
        return [
            'active' => Tab::make()
                ->label('Active Diagnoses')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->whereNull('deleted_at'))
                ->icon('heroicon-o-check-circle')
                ->badge(Diagnosis::cachedActiveCount()),

            'trashed' => Tab::make()
                ->label('Deactivated Diagnoses')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->onlyTrashed())
                ->icon('heroicon-o-x-circle')
                ->badge(Diagnosis::cachedTrashedCount()),

            'all' => Tab::make()
                ->label('All Diagnoses')
                ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes()->withTrashed())
                ->icon('heroicon-o-list-bullet')
                ->badge(Diagnosis::cachedTotalCount()),
        ];
    }


}