<?php

use App\Enums\ApplicationStatus;
use App\Enums\JobApplicationEventType;
use App\Filament\Resources\JobApplications\Pages\ListJobApplications;
use App\Models\Contact;
use App\Models\JobApplication;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user);

    Carbon::setTestNow('2026-09-13 10:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function createQuickActionApplication(
    string $company,
    ApplicationStatus $status = ApplicationStatus::Pending,
    ?string $sentAt = null,
): JobApplication {
    return JobApplication::query()->create([
        'company_id' => companyId($company),
        'job_title' => 'Full Stack Developer',
        'status' => $status,
        'sent_at' => $sentAt,
    ]);
}

function createQuickActionContact(string $name): Contact
{
    return Contact::query()->create([
        'name' => $name,
    ]);
}

test('inquiry sent keeps the application pending and creates the event with the selected contact', function () {
    $application = createQuickActionApplication('Inquiry Company');
    $contact = createQuickActionContact('Primary Contact');

    $application->contacts()->attach($contact->id, [
        'is_primary' => true,
    ]);

    Livewire::test(ListJobApplications::class)
        ->callAction(
            TestAction::make('inquirySent')->table($application),
            data: [
                'contact_id' => $contact->id,
                'inquiry_message' => 'Could you confirm whether the position is fully remote?',
                'next_action_at' => '2026-09-16',
            ],
        )
        ->assertHasNoFormErrors();

    $application->refresh();

    expect($application->status)
        ->toBe(ApplicationStatus::Pending)
        ->and($application->sent_at)
        ->toBeNull()
        ->and($application->next_step)
        ->toBe(__('job-applications.quick_actions.next_steps.wait_for_inquiry_response'))
        ->and($application->next_action_at?->toDateString())
        ->toBe('2026-09-16');

    $event = $application->events()
        ->latest('id')
        ->firstOrFail();

    expect($event->type)
        ->toBe(JobApplicationEventType::InquirySent)
        ->and($event->contact_id)
        ->toBe($contact->id)
        ->and($event->body)
        ->toBe('Could you confirm whether the position is fully remote?')
        ->and($event->status_from)
        ->toBe(ApplicationStatus::Pending)
        ->and($event->status_to)
        ->toBeNull()
        ->and($event->next_action_at?->toDateString())
        ->toBe('2026-09-16');
});

test('inquiry sent preselects the primary contact', function () {
    $application = createQuickActionApplication('Primary Contact Company');

    $secondaryContact = createQuickActionContact('Secondary Contact');
    $primaryContact = createQuickActionContact('Primary Contact');

    $application->contacts()->attach($secondaryContact->id, [
        'is_primary' => false,
    ]);

    $application->contacts()->attach($primaryContact->id, [
        'is_primary' => true,
    ]);

    Livewire::test(ListJobApplications::class)
        ->mountAction(
            TestAction::make('inquirySent')->table($application),
        )
        ->assertSchemaStateSet([
            'contact_id' => $primaryContact->id,
            'next_action_at' => '2026-09-16',
        ]);
});

test('existing quick actions assign the primary contact to generated events', function () {
    $application = createQuickActionApplication(
        'Response Company',
        ApplicationStatus::Sent,
        '2026-09-10',
    );

    $contact = createQuickActionContact('Primary Recruiter');

    $application->contacts()->attach($contact->id, [
        'is_primary' => true,
    ]);

    Livewire::test(ListJobApplications::class)
        ->callAction(
            TestAction::make('markAsResponded')->table($application),
        );

    $application->refresh();

    $event = $application->events()
        ->latest('id')
        ->firstOrFail();

    expect($application->status)
        ->toBe(ApplicationStatus::Responded)
        ->and($event->type)
        ->toBe(JobApplicationEventType::ResponseReceived)
        ->and($event->contact_id)
        ->toBe($contact->id);
});

test('existing quick actions leave the event contact empty when there is no primary contact', function () {
    $application = createQuickActionApplication(
        'No Primary Company',
        ApplicationStatus::Sent,
        '2026-09-10',
    );

    $contact = createQuickActionContact('Secondary Contact');

    $application->contacts()->attach($contact->id, [
        'is_primary' => false,
    ]);

    Livewire::test(ListJobApplications::class)
        ->callAction(
            TestAction::make('markAsResponded')->table($application),
        );

    $event = $application->events()
        ->latest('id')
        ->firstOrFail();

    expect($event->type)
        ->toBe(JobApplicationEventType::ResponseReceived)
        ->and($event->contact_id)
        ->toBeNull();
});
