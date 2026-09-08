<?php

namespace App\Filament\Resources\Interviews\Pages;

use App\Filament\Resources\Interviews\InterviewResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInterview extends EditRecord
{
    protected static string $resource = InterviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
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

        return __('actions.edit').' '.$recordTitle;
    }
}
