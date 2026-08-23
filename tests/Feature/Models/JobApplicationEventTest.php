<?php

use App\Enums\ApplicationStatus;
use App\Enums\InterviewType;
use App\Enums\JobApplicationEventType;
use App\Models\CvVersion;
use App\Models\JobApplication;

test('it synchronizes the application when an event is created', function () {
    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Pending,
    ]);

    $event = $jobApplication->events()->create([
        'type' => JobApplicationEventType::ManualNote,
        'occurred_at' => now(),
        'title' => 'Interview scheduled',
        'status_to' => ApplicationStatus::Interview,
        'next_action_at' => '2026-08-20',
    ]);

    $jobApplication->refresh();

    expect($event->status_from)
        ->toBe(ApplicationStatus::Pending)
        ->and($jobApplication->status)
        ->toBe(ApplicationStatus::Interview)
        ->and($jobApplication->next_step)
        ->toBe(ApplicationStatus::Interview->nextStep())
        ->and($jobApplication->next_action_at->toDateString())
        ->toBe('2026-08-20');
});

test('it synchronizes the application next step using the event content locale', function () {
    app()->setLocale('es');

    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Locale Test Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Pending,
    ]);

    $event = $jobApplication->events()->make([
        'type' => JobApplicationEventType::ManualNote,
        'occurred_at' => now(),
        'status_to' => ApplicationStatus::Interview,
    ]);

    $event->setLocale('en');
    $event->setTranslation('title', 'en', 'Interview scheduled');
    $event->save();

    $jobApplication->refresh();

    expect($jobApplication->getTranslation('next_step', 'en', false))
        ->toBe(__('job-applications.quick_actions.next_steps.prepare_interview', [], 'en'))
        ->and($jobApplication->getTranslation('next_step', 'es', false))
        ->toBeNull();
});

test('it preserves the status and next step when the event has no destination status', function () {
    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Sent,
        'next_step' => 'Custom next step',
        'next_action_at' => '2026-08-20',
    ]);

    $jobApplication->events()->create([
        'type' => JobApplicationEventType::ManualNote,
        'occurred_at' => now(),
        'title' => 'Manual note',
        'next_action_at' => '2026-08-25',
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::Sent)
        ->and($jobApplication->next_step)
        ->toBe('Custom next step')
        ->and($jobApplication->next_action_at->toDateString())
        ->toBe('2026-08-25');
});

test('it clears the next step and date for a final status', function () {
    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Interview,
        'next_step' => 'Prepare interview',
        'next_action_at' => '2026-08-20',
    ]);

    $jobApplication->events()->create([
        'type' => JobApplicationEventType::Rejected,
        'occurred_at' => now(),
        'title' => 'Application rejected',
        'status_to' => ApplicationStatus::Rejected,
        'next_action_at' => '2026-08-25',
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::Rejected)
        ->and($jobApplication->next_step)
        ->toBeNull()
        ->and($jobApplication->next_action_at)
        ->toBeNull();
});

test('it does not synchronize the application when an historical event is edited', function () {
    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Pending,
    ]);

    $event = $jobApplication->events()->create([
        'type' => JobApplicationEventType::ResponseReceived,
        'occurred_at' => now(),
        'title' => 'Response received',
        'status_to' => ApplicationStatus::Responded,
        'next_action_at' => '2026-08-20',
    ]);

    $jobApplication->forceFill([
        'status' => ApplicationStatus::TechnicalTest,
        'next_step' => 'Complete technical test',
        'next_action_at' => '2026-08-22',
    ])->save();

    $event->forceFill([
        'status_to' => ApplicationStatus::Hired,
        'next_action_at' => null,
    ])->save();

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::TechnicalTest)
        ->and($jobApplication->next_step)
        ->toBe('Complete technical test')
        ->and($jobApplication->next_action_at->toDateString())
        ->toBe('2026-08-22');
});

test('it stores translations for event content fields', function () {
    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Pending,
    ]);

    $event = $jobApplication->events()->create([
        'type' => JobApplicationEventType::ManualNote,
        'occurred_at' => now(),
        'title' => [
            'es' => 'Entrevista programada',
        ],
    ]);

    $event
        ->setTranslations('title', [
            'es' => 'Entrevista programada',
            'en' => 'Interview scheduled',
            'fr' => 'Entretien programmé',
        ])
        ->setTranslations('body', [
            'es' => 'Preparar la entrevista técnica',
            'en' => 'Prepare the technical interview',
            'fr' => 'Préparer l’entretien technique',
        ])
        ->save();

    $event->refresh();

    expect($event->getTranslation('title', 'es', false))
        ->toBe('Entrevista programada')
        ->and($event->getTranslation('title', 'en', false))
        ->toBe('Interview scheduled')
        ->and($event->getTranslation('title', 'fr', false))
        ->toBe('Entretien programmé')
        ->and($event->getTranslation('body', 'es', false))
        ->toBe('Preparar la entrevista técnica')
        ->and($event->getTranslation('body', 'en', false))
        ->toBe('Prepare the technical interview')
        ->and($event->getTranslation('body', 'fr', false))
        ->toBe('Préparer l’entretien technique');
});

test('it returns event content for the selected locale', function () {
    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Pending,
    ]);

    $event = $jobApplication->events()->create([
        'type' => JobApplicationEventType::ManualNote,
        'occurred_at' => now(),
        'title' => [
            'es' => 'Nota manual',
            'en' => 'Manual note',
        ],
        'body' => [
            'es' => 'Contenido en español',
            'en' => 'Content in English',
        ],
    ]);

    $event->setLocale('es');

    expect($event->title)
        ->toBe('Nota manual')
        ->and($event->body)
        ->toBe('Contenido en español');

    $event->setLocale('en');

    expect($event->title)
        ->toBe('Manual note')
        ->and($event->body)
        ->toBe('Content in English');
});

test('it sets the application sent date when an event changes the status to sent', function () {
    $cvVersion = CvVersion::query()->create([
        'name' => 'CV Sent Event',
        'language' => 'spanish',
    ]);

    $jobApplication = JobApplication::query()->create([
        'cv_version_id' => $cvVersion->id,
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Pending,
    ]);

    $jobApplication->events()->create([
        'type' => JobApplicationEventType::ApplicationSent,
        'occurred_at' => '2026-08-15 10:30:00',
        'title' => 'Application sent',
        'status_to' => ApplicationStatus::Sent,
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::Sent)
        ->and($jobApplication->sent_at?->toDateString())
        ->toBe('2026-08-15');
});

test('it preserves the existing application sent date when another event changes the status to sent', function () {
    $cvVersion = CvVersion::query()->create([
        'name' => 'CV Existing Sent Date',
        'language' => 'spanish',
    ]);

    $jobApplication = JobApplication::query()->create([
        'cv_version_id' => $cvVersion->id,
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Responded,
        'sent_at' => '2026-08-10',
    ]);

    $jobApplication->events()->create([
        'type' => JobApplicationEventType::ApplicationSent,
        'occurred_at' => '2026-08-15 10:30:00',
        'title' => 'Application sent again',
        'status_to' => ApplicationStatus::Sent,
    ]);

    $jobApplication->refresh();

    expect($jobApplication->sent_at?->toDateString())
        ->toBe('2026-08-10');
});

test('it does not synchronize the application when an older historical event is created', function () {
    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Historical Event Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Pending,
    ]);

    $jobApplication->events()->create([
        'type' => JobApplicationEventType::ResponseReceived,
        'occurred_at' => '2026-08-20 10:00:00',
        'title' => 'Response received',
        'status_to' => ApplicationStatus::Responded,
        'next_action_at' => '2026-08-22',
    ]);

    $jobApplication->events()->create([
        'type' => JobApplicationEventType::ManualNote,
        'occurred_at' => '2026-08-10 10:00:00',
        'title' => 'Historical interview',
        'status_to' => ApplicationStatus::Interview,
        'next_action_at' => '2026-08-12',
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::Responded)
        ->and($jobApplication->next_step)
        ->toBe(ApplicationStatus::Responded->nextStep())
        ->and($jobApplication->next_action_at?->toDateString())
        ->toBe('2026-08-22');
});

test('it derives the previous status chronologically for an historical event', function () {
    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Historical Status Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Pending,
    ]);

    $jobApplication->events()->create([
        'type' => JobApplicationEventType::ApplicationSent,
        'occurred_at' => '2026-08-10 10:00:00',
        'title' => 'Application sent',
        'status_to' => ApplicationStatus::Sent,
    ]);

    $jobApplication->events()->create([
        'type' => JobApplicationEventType::ResponseReceived,
        'occurred_at' => '2026-08-20 10:00:00',
        'title' => 'Response received',
        'status_to' => ApplicationStatus::Responded,
    ]);

    $historicalEvent = $jobApplication->events()->create([
        'type' => JobApplicationEventType::ManualNote,
        'occurred_at' => '2026-08-15 10:00:00',
        'title' => 'Historical interview',
        'status_to' => ApplicationStatus::Interview,
    ]);

    expect($historicalEvent->status_from)
        ->toBe(ApplicationStatus::Sent);
});

test('it preserves an explicit previous status for an historical event', function () {
    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Historical Explicit Status Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Pending,
    ]);

    $jobApplication->events()->create([
        'type' => JobApplicationEventType::ResponseReceived,
        'occurred_at' => '2026-08-20 10:00:00',
        'title' => 'Response received',
        'status_to' => ApplicationStatus::Responded,
    ]);

    $historicalEvent = $jobApplication->events()->create([
        'type' => JobApplicationEventType::ManualNote,
        'occurred_at' => '2026-08-10 10:00:00',
        'title' => 'Historical note',
        'status_from' => ApplicationStatus::Sent,
    ]);

    expect($historicalEvent->status_from)
        ->toBe(ApplicationStatus::Sent);
});

test('it does not synchronize the application when a newer interview exists', function () {
    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Historical Event With Interview Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Pending,
    ]);

    $jobApplication->events()->create([
        'type' => JobApplicationEventType::ApplicationSent,
        'occurred_at' => '2026-08-05 10:00:00',
        'title' => 'Application sent',
        'status_to' => ApplicationStatus::Sent,
    ]);

    $jobApplication->interviews()->create([
        'interview_at' => '2026-08-20 10:30:00',
        'interview_type' => InterviewType::Hr,
    ]);

    $historicalEvent = $jobApplication->events()->create([
        'type' => JobApplicationEventType::ResponseReceived,
        'occurred_at' => '2026-08-10 10:00:00',
        'title' => 'Historical response',
        'status_to' => ApplicationStatus::Responded,
        'next_action_at' => '2026-08-12',
    ]);

    $jobApplication->refresh();

    expect($historicalEvent->status_from)
        ->toBe(ApplicationStatus::Sent)
        ->and($jobApplication->status)
        ->toBe(ApplicationStatus::Interview)
        ->and($jobApplication->next_action_at?->toDateString())
        ->toBe('2026-08-20');
});

test('it derives the previous status from an earlier interview when creating an historical event', function () {
    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Historical Event After Interview Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Pending,
    ]);

    $jobApplication->events()->create([
        'type' => JobApplicationEventType::ApplicationSent,
        'occurred_at' => '2026-08-05 10:00:00',
        'title' => 'Application sent',
        'status_to' => ApplicationStatus::Sent,
    ]);

    $jobApplication->interviews()->create([
        'interview_at' => '2026-08-10 10:30:00',
        'interview_type' => InterviewType::Hr,
    ]);

    $jobApplication->events()->create([
        'type' => JobApplicationEventType::TechnicalTest,
        'occurred_at' => '2026-08-20 10:00:00',
        'title' => 'Technical test assigned',
        'status_to' => ApplicationStatus::TechnicalTest,
        'next_action_at' => '2026-08-25',
    ]);

    $historicalEvent = $jobApplication->events()->create([
        'type' => JobApplicationEventType::ManualNote,
        'occurred_at' => '2026-08-15 10:00:00',
        'title' => 'Historical note',
    ]);

    $jobApplication->refresh();

    expect($historicalEvent->status_from)
        ->toBe(ApplicationStatus::Interview)
        ->and($jobApplication->status)
        ->toBe(ApplicationStatus::TechnicalTest)
        ->and($jobApplication->next_action_at?->toDateString())
        ->toBe('2026-08-25');
});
