<?php

namespace App\Filament\Resources\AtencionMedicaResource\Pages;

use App\Filament\Resources\AtencionMedicaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAtencionMedicas extends ManageRecords
{
    protected static string $resource = AtencionMedicaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
