<?php

namespace App\Filament\Resources\BusResource\Pages;

use App\Filament\Resources\BusResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateBus extends CreateRecord
{
    protected static string $resource = BusResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (Auth::user()->role === 'manager') {
            $data['organizations'] = [Auth::user()->organization_id];
        }
        
        return $data;
    }
}
