<?php

namespace App\Filament\Resources\Interviews\Pages;

use App\Filament\Resources\Interviews\InterviewResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;

class ViewInterview extends ViewRecord
{
    use Translatable;

    protected static string $resource = InterviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            EditAction::make(),
        ];
    }

    public function getTitle(): string
    {
        $jobApplication = $this->getRecord()->jobApplication;

        $recordTitle = collect([
            $jobApplication?->company_name,
            $jobApplication?->job_title,
        ])
            ->filter()
            ->implode(' — ');

        return __('actions.view').' '.$recordTitle;
    }
}
