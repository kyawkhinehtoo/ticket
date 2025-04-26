<?php

namespace App\Filament\Client\Resources\IncidentResource\Pages;

use App\Filament\Client\Resources\IncidentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIncident extends EditRecord
{
    protected static string $resource = IncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
