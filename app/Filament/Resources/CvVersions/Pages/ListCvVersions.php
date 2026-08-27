<?php

namespace App\Filament\Resources\CvVersions\Pages;

use App\Filament\Resources\CvVersions\CvVersionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCvVersions extends ListRecords
{
    protected static string $resource = CvVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
