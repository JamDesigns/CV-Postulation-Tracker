<?php

namespace App\Filament\Resources\TechnicalDossierVersions\Pages;

use App\Filament\Resources\TechnicalDossierVersions\TechnicalDossierVersionResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateTechnicalDossierVersion extends CreateRecord
{
    use Translatable;

    protected static string $resource = TechnicalDossierVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
        ];
    }
}
