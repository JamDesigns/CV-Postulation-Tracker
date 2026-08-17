<?php

namespace App\Filament\Resources\TechnicalDossierVersions\Pages;

use App\Filament\Resources\TechnicalDossierVersions\TechnicalDossierVersionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;

class ViewTechnicalDossierVersion extends ViewRecord
{
    use Translatable;

    protected static string $resource = TechnicalDossierVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            EditAction::make(),
        ];
    }
}
