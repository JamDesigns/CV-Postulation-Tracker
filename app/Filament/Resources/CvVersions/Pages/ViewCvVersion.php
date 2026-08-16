<?php

namespace App\Filament\Resources\CvVersions\Pages;

use App\Filament\Resources\CvVersions\CvVersionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;

class ViewCvVersion extends ViewRecord
{
    use Translatable;

    protected static string $resource = CvVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            EditAction::make(),
        ];
    }
}
