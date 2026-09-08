<?php

use App\Enums\ApplicationStatus;
use App\Enums\InterviewType;
use App\Enums\JobApplicationEventType;
use App\Models\Contact;
use App\Models\CvVersion;
use App\Models\JobApplication;
use Illuminate\Validation\ValidationException;

test('it synchronizes the application when an event is created', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
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
        'company_id' => companyId('Test Company'),
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
        'company_id' => companyId('Test Company'),
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
        'company_id' => companyId('Test Company'),
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

test('it sets the application sent date when an event changes the status to sent', function () {
    $cvVersion = CvVersion::query()->create([
        'name' => 'CV Sent Event',
        'language' => 'spanish',
    ]);

    $jobApplication = JobApplication::query()->create([
        'cv_version_id' => $cvVersion->id,
        'company_id' => companyId('Test Company'),
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
        'company_id' => companyId('Test Company'),
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
        'company_id' => companyId('Historical Event Company'),
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
        'company_id' => companyId('Historical Status Company'),
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
        'company_id' => companyId('Historical Explicit Status Company'),
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
        'company_id' => companyId('Historical Event With Interview Company'),
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
        'company_id' => companyId('Historical Event After Interview Company'),
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

test('it allows an event to reference a contact attached to its application', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Contact Event Company'),
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Pending,
    ]);

    $contact = Contact::query()->create([
        'name' => 'Attached Contact',
    ]);

    $jobApplication->contacts()->attach($contact->id);

    $event = $jobApplication->events()->create([
        'type' => JobApplicationEventType::ManualNote,
        'occurred_at' => now(),
        'title' => 'Contact event',
        'contact_id' => $contact->id,
    ]);

    expect($event->contact_id)
        ->toBe($contact->id)
        ->and($event->contact?->is($contact))
        ->toBeTrue();
});

test('it rejects a contact that is not attached to the event application', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Invalid Contact Event Company'),
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Pending,
    ]);

    $contact = Contact::query()->create([
        'name' => 'Unattached Contact',
    ]);

    expect(fn () => $jobApplication->events()->create([
        'type' => JobApplicationEventType::ManualNote,
        'occurred_at' => now(),
        'title' => 'Invalid contact event',
        'contact_id' => $contact->id,
    ]))->toThrow(ValidationException::class);
});

test('it rejects changing an event contact to one not attached to its application', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Changed Contact Event Company'),
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Pending,
    ]);

    $attachedContact = Contact::query()->create([
        'name' => 'Attached Contact',
    ]);

    $unattachedContact = Contact::query()->create([
        'name' => 'Unattached Contact',
    ]);

    $jobApplication->contacts()->attach($attachedContact->id);

    $event = $jobApplication->events()->create([
        'type' => JobApplicationEventType::ManualNote,
        'occurred_at' => now(),
        'title' => 'Original contact event',
        'contact_id' => $attachedContact->id,
    ]);

    $event->contact_id = $unattachedContact->id;

    expect(fn () => $event->save())
        ->toThrow(ValidationException::class);
});

test('it allows an historical event to be edited after its contact is detached', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Historical Contact Event Company'),
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Pending,
    ]);

    $contact = Contact::query()->create([
        'name' => 'Historical Contact',
    ]);

    $jobApplication->contacts()->attach($contact->id);

    $event = $jobApplication->events()->create([
        'type' => JobApplicationEventType::ManualNote,
        'occurred_at' => now(),
        'title' => 'Original historical event',
        'contact_id' => $contact->id,
    ]);

    $jobApplication->contacts()->detach($contact->id);

    $event->title = 'Updated historical event';
    $event->save();

    $event->refresh();

    expect($event->title)
        ->toBe('Updated historical event')
        ->and($event->contact_id)
        ->toBe($contact->id);
});

test('it preserves an event and clears its contact when the contact is deleted', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Deleted Contact Event Company'),
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Pending,
    ]);

    $contact = Contact::query()->create([
        'name' => 'Deleted Contact',
    ]);

    $jobApplication->contacts()->attach($contact->id);

    $event = $jobApplication->events()->create([
        'type' => JobApplicationEventType::ManualNote,
        'occurred_at' => now(),
        'title' => 'Event with deleted contact',
        'contact_id' => $contact->id,
    ]);

    $eventId = $event->id;

    $contact->delete();

    $event = $jobApplication->events()->findOrFail($eventId);

    expect($event->exists)
        ->toBeTrue()
        ->and($event->contact_id)
        ->toBeNull();
});
