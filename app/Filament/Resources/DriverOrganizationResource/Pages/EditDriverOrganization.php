<?php

namespace App\Filament\Resources\DriverOrganizationResource\Pages;

use App\Filament\Resources\DriverOrganizationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDriverOrganization extends EditRecord
{
    protected static string $resource = DriverOrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 