<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->withoutGlobalScopes()
            ->orderBy('name');
    }

    public function getTabs(): array
    {

        return [
            'active' => Tab::make()
                ->label('Active Users')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNull('deleted_at'))
                ->icon('heroicon-o-check-circle')
                ->badge(static::getModel()::whereNull('deleted_at')->count()),

            'trashed' => Tab::make()
                ->label('Deactivated Users')
                ->modifyQueryUsing(fn(Builder $query) => $query->onlyTrashed())
                ->icon('heroicon-o-x-circle')
                ->badge(static::getModel()::onlyTrashed()->count()),

            'all' => Tab::make()
                ->label('All Users')
                ->modifyQueryUsing(fn(Builder $query) => $query->withTrashed())
                ->icon('heroicon-o-list-bullet'),
        ];
    }

    protected function getTableRecordUrlRecord(): ?string
    {
        return $this->record->id;
    }


    protected function getTableRecordUrl(): ?string
    {
        return static::getResource()::getUrl('edit');
    }
}
