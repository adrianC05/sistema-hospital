<?php

namespace App\Filament\Resources\DocumentoTransaccionResource\Pages;

use App\Filament\Resources\DocumentoTransaccionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDocumentoTransaccions extends ManageRecords
{
    protected static string $resource = DocumentoTransaccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
