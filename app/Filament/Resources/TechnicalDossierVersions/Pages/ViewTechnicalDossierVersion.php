<?php

namespace App\Filament\Resources\TechnicalDossierVersions\Pages;

use App\Filament\Resources\TechnicalDossierVersions\TechnicalDossierVersionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTechnicalDossierVersion extends ViewRecord
{
    protected static string $resource = TechnicalDossierVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
