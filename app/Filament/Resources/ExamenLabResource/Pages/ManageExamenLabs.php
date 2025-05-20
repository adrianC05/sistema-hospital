<?php

namespace App\Filament\Resources\ExamenLabResource\Pages;

use App\Filament\Resources\ExamenLabResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageExamenLabs extends ManageRecords
{
    protected static string $resource = ExamenLabResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
