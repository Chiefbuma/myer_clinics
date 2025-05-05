<?php

namespace App\Filament\Resources\MedicationUseResource\Pages;

use App\Filament\Resources\MedicationUseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedicationUse extends EditRecord
{
    protected static string $resource = MedicationUseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
