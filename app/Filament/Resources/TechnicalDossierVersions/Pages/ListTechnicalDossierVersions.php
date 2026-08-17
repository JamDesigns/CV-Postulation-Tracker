<?php

namespace App\Filament\Resources\TechnicalDossierVersions\Pages;

use App\Filament\Resources\TechnicalDossierVersions\TechnicalDossierVersionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class ListTechnicalDossierVersions extends ListRecords
{
    use Translatable;

    protected static string $resource = TechnicalDossierVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
