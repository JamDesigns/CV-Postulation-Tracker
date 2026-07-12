<?php

namespace App\Filament\Resources\CvVersions\Pages;

use App\Filament\Resources\CvVersions\CvVersionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCvVersion extends EditRecord
{
    protected static string $resource = CvVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
