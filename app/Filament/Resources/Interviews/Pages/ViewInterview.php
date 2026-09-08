<?php

namespace App\Filament\Resources\Interviews\Pages;

use App\Filament\Resources\Interviews\InterviewResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInterview extends ViewRecord
{
    protected static string $resource = InterviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function getTitle(): string
    {
        $jobApplication = $this->getRecord()->jobApplication;

        $recordTitle = collect([
            $jobApplication?->company?->name,
            $jobApplication?->job_title,
        ])
            ->filter()
            ->implode(' — ');

        return __('actions.view').' '.$recordTitle;
    }
}
