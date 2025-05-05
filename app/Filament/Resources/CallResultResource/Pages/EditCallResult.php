<?php

namespace App\Filament\Resources\CallResultResource\Pages;

use App\Filament\Resources\CallResultResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCallResult extends EditRecord
{
    protected static string $resource = CallResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
