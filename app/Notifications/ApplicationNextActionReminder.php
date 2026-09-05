<?php

namespace App\Notifications;

use App\Filament\Resources\JobApplications\JobApplicationResource;
use App\Models\JobApplication;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApplicationNextActionReminder extends Notification
{
    use Queueable;

    public function __construct(
        public JobApplication $jobApplication,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(User $notifiable): array
    {
        $message = FilamentNotification::make()
            ->title(__('job-applications.reminders.next_action.title', [
                'company' => $this->jobApplication->company_name,
                'job' => $this->jobApplication->job_title,
            ]))
            ->body($this->jobApplication->next_step)
            ->warning()
            ->actions([
                Action::make('view')
                    ->label(__('job-applications.reminders.next_action.view'))
                    ->url(JobApplicationResource::getUrl(
                        'view',
                        ['record' => $this->jobApplication],
                        panel: 'admin',
                    ))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();

        return [
            ...$message,
            'job_application_id' => $this->jobApplication->getKey(),
            'next_action_at' => $this->jobApplication->next_action_at?->toDateString(),
        ];
    }
}
