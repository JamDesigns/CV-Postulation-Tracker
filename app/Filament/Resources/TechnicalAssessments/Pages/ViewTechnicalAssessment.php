<?php

namespace App\Filament\Resources\TechnicalAssessments\Pages;

use App\Filament\Resources\TechnicalAssessments\TechnicalAssessmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTechnicalAssessment extends ViewRecord
{
    protected static string $resource = TechnicalAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
