<?php

namespace App\Filament\Resources\DriverOrganizationResource\Pages;

use App\Filament\Resources\DriverOrganizationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDriverOrganizations extends ListRecords
{
    protected static string $resource = DriverOrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
} 