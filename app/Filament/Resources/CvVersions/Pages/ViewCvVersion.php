<?php

namespace App\Filament\Resources\CvVersions\Pages;

use App\Filament\Resources\CvVersions\CvVersionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCvVersion extends ViewRecord
{
    protected static string $resource = CvVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
