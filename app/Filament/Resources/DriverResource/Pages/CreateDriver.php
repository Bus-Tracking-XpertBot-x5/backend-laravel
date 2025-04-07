<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDriver extends CreateRecord
{
    protected static bool $canCreateAnother = false;

    protected static string $resource = DriverResource::class;

    protected function afterCreate(): void
    {
        if (Auth::user()->role === 'manager') {
            $this->record->organizations()->attach(Auth::user()->organization_id);
        }
    }
}
