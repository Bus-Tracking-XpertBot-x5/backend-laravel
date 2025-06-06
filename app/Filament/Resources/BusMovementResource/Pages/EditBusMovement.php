<?php

namespace App\Filament\Resources\BusMovementResource\Pages;

use App\Filament\Resources\BusMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBusMovement extends EditRecord
{
    protected static string $resource = BusMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
