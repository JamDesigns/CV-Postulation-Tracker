<?php

namespace App\Filament\Resources\TechnicalDossierVersions\Pages;

use App\Filament\Resources\TechnicalDossierVersions\TechnicalDossierVersionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTechnicalDossierVersions extends ListRecords
{
    protected static string $resource = TechnicalDossierVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
