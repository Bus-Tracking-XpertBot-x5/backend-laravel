<?php

namespace App\Filament\Resources\DriverOrganizationResource\Pages;

use App\Filament\Resources\DriverOrganizationResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDriverOrganization extends CreateRecord
{
    protected static string $resource = DriverOrganizationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (Auth::user()->role === 'manager') {
            $data['organization_id'] = Auth::user()->organization_id;
        }
        
        return $data;
    }
} 