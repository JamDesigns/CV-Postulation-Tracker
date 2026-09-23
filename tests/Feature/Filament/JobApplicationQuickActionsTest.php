<?php

use App\Enums\ApplicationStatus;
use App\Enums\CommunicationChannel;
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
                'communication_channel' => CommunicationChannel::Linkedin->value,
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
        ->and($event->communication_channel)
        ->toBe(CommunicationChannel::Linkedin)
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

test('mark as responded stores the selected contact and communication details', function () {
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
            data: [
                'contact_id' => $contact->id,
                'communication_channel' => CommunicationChannel::Email->value,
                'communication_detail' => 'Thank you for applying. We would like to continue the process.',
                'next_step' => 'Review the response',
                'next_action_at' => '2026-09-15',
            ],
        )
        ->assertHasNoFormErrors();

    $application->refresh();

    $event = $application->events()
        ->latest('id')
        ->firstOrFail();

    expect($application->status)
        ->toBe(ApplicationStatus::Responded)
        ->and($event->type)
        ->toBe(JobApplicationEventType::ResponseReceived)
        ->and($event->contact_id)
        ->toBe($contact->id)
        ->and($event->communication_channel)
        ->toBe(CommunicationChannel::Email)
        ->and($event->body)
        ->toBe('Thank you for applying. We would like to continue the process.');
});

test('mark as responded allows the event contact to remain empty', function () {
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
            data: [
                'communication_channel' => CommunicationChannel::Email->value,
                'communication_detail' => 'Generic company response without a named contact.',
                'next_step' => 'Review the response',
                'next_action_at' => '2026-09-15',
            ],
        )
        ->assertHasNoFormErrors();

    $event = $application->events()
        ->latest('id')
        ->firstOrFail();

    expect($event->type)
        ->toBe(JobApplicationEventType::ResponseReceived)
        ->and($event->contact_id)
        ->toBeNull()
        ->and($event->communication_channel)
        ->toBe(CommunicationChannel::Email)
        ->and($event->body)
        ->toBe('Generic company response without a named contact.');
});

test('register communication creates a received communication without changing application status', function () {
    $application = createQuickActionApplication(
        'Generic Communication Company',
        ApplicationStatus::Pending,
    );

    Livewire::test(ListJobApplications::class)
        ->callAction(
            TestAction::make('registerCommunication')->table($application),
            data: [
                'communication_direction' => JobApplicationEventType::CommunicationReceived->value,
                'communication_channel' => CommunicationChannel::Email->value,
                'communication_detail' => 'We have received your message and will review it shortly.',
                'next_step' => 'Wait for the recruiter response',
                'next_action_at' => '2026-09-17',
            ],
        )
        ->assertHasNoFormErrors();

    $application->refresh();

    $event = $application->events()
        ->latest('id')
        ->firstOrFail();

    expect($application->status)
        ->toBe(ApplicationStatus::Pending)
        ->and($application->next_step)
        ->toBe('Wait for the recruiter response')
        ->and($application->next_action_at?->toDateString())
        ->toBe('2026-09-17')
        ->and($event->type)
        ->toBe(JobApplicationEventType::CommunicationReceived)
        ->and($event->communication_channel)
        ->toBe(CommunicationChannel::Email)
        ->and($event->contact_id)
        ->toBeNull()
        ->and($event->body)
        ->toBe('We have received your message and will review it shortly.')
        ->and($event->status_from)
        ->toBe(ApplicationStatus::Pending)
        ->and($event->status_to)
        ->toBeNull();
});

test('mark as technical test stores communication details and changes application status', function () {
    $application = createQuickActionApplication(
        'Technical Test Company',
        ApplicationStatus::Responded,
        '2026-09-10',
    );

    $contact = createQuickActionContact('Technical Recruiter');

    $application->contacts()->attach($contact->id, [
        'is_primary' => true,
    ]);

    Livewire::test(ListJobApplications::class)
        ->callAction(
            TestAction::make('markAsTechnicalTest')->table($application),
            data: [
                'contact_id' => $contact->id,
                'communication_channel' => CommunicationChannel::Email->value,
                'communication_detail' => 'We are sending you the technical test. Please complete it before the deadline.',
                'next_step' => 'Complete the technical test',
                'next_action_at' => '2026-09-20',
                'notes_to_append' => 'Repository access received.',
            ],
        )
        ->assertHasNoFormErrors();

    $application->refresh();

    $event = $application->events()
        ->latest('id')
        ->firstOrFail();

    expect($application->status)
        ->toBe(ApplicationStatus::TechnicalTest)
        ->and($application->next_step)
        ->toBe('Complete the technical test')
        ->and($application->next_action_at?->toDateString())
        ->toBe('2026-09-20')
        ->and($event->type)
        ->toBe(JobApplicationEventType::TechnicalTest)
        ->and($event->contact_id)
        ->toBe($contact->id)
        ->and($event->communication_channel)
        ->toBe(CommunicationChannel::Email)
        ->and($event->body)
        ->toBe(
            "We are sending you the technical test. Please complete it before the deadline.\n\nRepository access received.",
        )
        ->and($event->status_from)
        ->toBe(ApplicationStatus::Responded)
        ->and($event->status_to)
        ->toBe(ApplicationStatus::TechnicalTest);
});

test('mark follow up sent stores communication details and changes application status', function () {
    $application = createQuickActionApplication(
        'Follow Up Company',
        ApplicationStatus::Responded,
        '2026-09-10',
    );

    $contact = createQuickActionContact('Recruiter');

    $application->contacts()->attach($contact->id, [
        'is_primary' => true,
    ]);

    Livewire::test(ListJobApplications::class)
        ->callAction(
            TestAction::make('markFollowUpSent')->table($application),
            data: [
                'contact_id' => $contact->id,
                'communication_channel' => CommunicationChannel::Linkedin->value,
                'communication_detail' => 'Hello, I wanted to follow up on the status of the selection process.',
                'next_step' => 'Wait for follow-up response',
                'next_action_at' => '2026-09-21',
            ],
        )
        ->assertHasNoFormErrors();

    $application->refresh();

    $event = $application->events()
        ->latest('id')
        ->firstOrFail();

    expect($application->status)
        ->toBe(ApplicationStatus::FollowUpSent)
        ->and($application->next_step)
        ->toBe('Wait for follow-up response')
        ->and($application->next_action_at?->toDateString())
        ->toBe('2026-09-21')
        ->and($event->type)
        ->toBe(JobApplicationEventType::FollowUpSent)
        ->and($event->contact_id)
        ->toBe($contact->id)
        ->and($event->communication_channel)
        ->toBe(CommunicationChannel::Linkedin)
        ->and($event->body)
        ->toBe('Hello, I wanted to follow up on the status of the selection process.')
        ->and($event->status_from)
        ->toBe(ApplicationStatus::Responded)
        ->and($event->status_to)
        ->toBe(ApplicationStatus::FollowUpSent);
});

test('inquiry sent works without an existing contact', function () {
    $application = createQuickActionApplication(
        'Inquiry Without Contact Company',
        ApplicationStatus::Pending,
    );

    Livewire::test(ListJobApplications::class)
        ->callAction(
            TestAction::make('inquirySent')->table($application),
            data: [
                'communication_channel' => CommunicationChannel::Email->value,
                'inquiry_message' => 'Could you confirm the expected work mode for this position?',
                'next_action_at' => '2026-09-16',
            ],
        )
        ->assertHasNoFormErrors();

    $application->refresh();

    $event = $application->events()
        ->latest('id')
        ->firstOrFail();

    expect($application->status)
        ->toBe(ApplicationStatus::Pending)
        ->and($event->type)
        ->toBe(JobApplicationEventType::InquirySent)
        ->and($event->contact_id)
        ->toBeNull()
        ->and($event->communication_channel)
        ->toBe(CommunicationChannel::Email)
        ->and($event->body)
        ->toBe('Could you confirm the expected work mode for this position?');
});
