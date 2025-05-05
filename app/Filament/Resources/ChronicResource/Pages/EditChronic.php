<?php

namespace App\Filament\Resources\ChronicResource\Pages;

use App\Filament\Resources\ChronicResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChronic extends EditRecord
{
    protected static string $resource = ChronicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
