<?php

namespace App\Filament\Resources\MainConResource\Pages;

use App\Filament\Resources\MainConResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMainCon extends EditRecord
{
    protected static string $resource = MainConResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
