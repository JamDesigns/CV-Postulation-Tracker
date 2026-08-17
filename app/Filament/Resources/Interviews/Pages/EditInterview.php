<?php

namespace App\Filament\Resources\Interviews\Pages;

use App\Filament\Resources\Interviews\InterviewResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;

class EditInterview extends EditRecord
{
    use Translatable;

    protected static string $resource = InterviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            ViewAction::make(),
            DeleteAction::make(),
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

        return __('actions.edit').' '.$recordTitle;
    }
}
