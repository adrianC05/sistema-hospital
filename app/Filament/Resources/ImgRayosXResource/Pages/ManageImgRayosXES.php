<?php

namespace App\Filament\Resources\ImgRayosXResource\Pages;

use App\Filament\Resources\ImgRayosXResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageImgRayosXES extends ManageRecords
{
    protected static string $resource = ImgRayosXResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
