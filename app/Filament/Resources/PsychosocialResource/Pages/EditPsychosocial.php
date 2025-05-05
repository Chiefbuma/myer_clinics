<?php

namespace App\Filament\Resources\PsychosocialResource\Pages;

use App\Filament\Resources\PsychosocialResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPsychosocial extends EditRecord
{
    protected static string $resource = PsychosocialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
