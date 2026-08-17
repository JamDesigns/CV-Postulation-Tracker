<?php

use App\Enums\ApplicationStatus;
use App\Enums\JobApplicationEventType;
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
