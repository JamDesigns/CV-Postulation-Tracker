<?php

namespace App\Filament\Resources\TechnicalAssessments\Pages;

use App\Filament\Resources\TechnicalAssessments\TechnicalAssessmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTechnicalAssessments extends ListRecords
{
    protected static string $resource = TechnicalAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
