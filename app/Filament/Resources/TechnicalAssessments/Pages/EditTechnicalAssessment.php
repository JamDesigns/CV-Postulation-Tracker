<?php

namespace App\Filament\Resources\TechnicalAssessments\Pages;

use App\Filament\Resources\TechnicalAssessments\TechnicalAssessmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTechnicalAssessment extends EditRecord
{
    protected static string $resource = TechnicalAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
