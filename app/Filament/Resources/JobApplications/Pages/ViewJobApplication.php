<?php

namespace App\Filament\Resources\JobApplications\Pages;

use App\Filament\Resources\JobApplications\JobApplicationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;
use Livewire\Attributes\On;

class ViewJobApplication extends ViewRecord
{
    use Translatable;

    protected static string $resource = JobApplicationResource::class;

    #[On('job-application-updated')]
    public function refreshJobApplication(): void
    {
        $this->getRecord()->refresh();

        $this->refreshFormData([
            'status',
            'next_step',
            'next_action_at',
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            EditAction::make(),
        ];
    }
}
