<?php

namespace App\Filament\Resources\PhysiotherapyResource\Pages;

use App\Filament\Resources\PhysiotherapyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPhysiotherapy extends EditRecord
{
    protected static string $resource = PhysiotherapyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
