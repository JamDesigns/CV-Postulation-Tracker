<?php

use App\Enums\TechnicalAssessmentDirection;
use App\Enums\TechnicalAssessmentStatus;
use App\Enums\TechnicalAssessmentType;
use App\Filament\Resources\JobApplications\Pages\EditJobApplication;
use App\Filament\Resources\JobApplications\RelationManagers\TechnicalAssessmentsRelationManager;
use App\Models\JobApplication;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(function (): void {
    $user = new User([
        'name' => 'Technical Assessments Relation Manager Test User',
        'email' => 'technical-assessments-relation-manager@example.com',
        'password' => 'password',
    ]);

    $user->save();

    Livewire::actingAs($user);
});

test('it can load the technical assessments relation manager', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Technical Assessments Relation Manager Test Company'),
        'job_title' => 'Backend Developer',
    ]);

    Livewire::test(TechnicalAssessmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->assertOk()
        ->assertCountTableRecords(0);
});

test('it only shows technical assessments from the owner job application', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Owner Technical Assessment Company'),
        'job_title' => 'Backend Developer',
    ]);

    $otherJobApplication = JobApplication::query()->create([
        'company_id' => companyId('Other Technical Assessment Company'),
        'job_title' => 'Frontend Developer',
    ]);

    $assessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
        'received_at' => '2026-08-15 09:00:00',
    ]);

    $otherAssessment = $otherJobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
        'received_at' => '2026-08-20 09:00:00',
    ]);

    Livewire::test(TechnicalAssessmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->assertCanSeeTableRecords([$assessment])
        ->assertCanNotSeeTableRecords([$otherAssessment]);
});

test('it can create a technical assessment for the owner job application', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Create Technical Assessment Company'),
        'job_title' => 'Backend Developer',
    ]);

    Livewire::test(TechnicalAssessmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->callAction(
            TestAction::make('create')->table(),
            data: [
                'test_type' => TechnicalAssessmentType::AtHome->value,
                'direction' => TechnicalAssessmentDirection::Received->value,
                'status' => TechnicalAssessmentStatus::Pending->value,
                'received_at' => '2026-08-15 09:00:00',
                'deadline_at' => '2026-08-25 23:59:00',
                'notes' => 'Technical assessment created from relation manager test.',
            ],
        )
        ->assertHasNoFormErrors();

    $assessment = $jobApplication->technicalAssessments()->sole();

    expect($assessment->job_application_id)
        ->toBe($jobApplication->id)
        ->and($assessment->test_type)
        ->toBe(TechnicalAssessmentType::AtHome)
        ->and($assessment->direction)
        ->toBe(TechnicalAssessmentDirection::Received)
        ->and($assessment->status)
        ->toBe(TechnicalAssessmentStatus::Pending)
        ->and($assessment->deadline_at?->format('Y-m-d H:i'))
        ->toBe('2026-08-25 23:59')
        ->and($assessment->notes)
        ->toBe('Technical assessment created from relation manager test.');
});

test('it requires a modality for live technical assessments', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Live Technical Assessment Company'),
        'job_title' => 'Backend Developer',
    ]);

    Livewire::test(TechnicalAssessmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->callAction(
            TestAction::make('create')->table(),
            data: [
                'test_type' => TechnicalAssessmentType::Live->value,
                'modality' => null,
                'direction' => TechnicalAssessmentDirection::Received->value,
                'status' => TechnicalAssessmentStatus::Pending->value,
            ],
        )
        ->assertHasFormErrors(['modality' => 'required']);

    expect($jobApplication->technicalAssessments()->count())
        ->toBe(0);
});

test('it requires a result for finished technical assessments', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Finished Technical Assessment Validation Company'),
        'job_title' => 'Backend Developer',
    ]);

    Livewire::test(TechnicalAssessmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->callAction(
            TestAction::make('create')->table(),
            data: [
                'test_type' => TechnicalAssessmentType::AtHome->value,
                'direction' => TechnicalAssessmentDirection::Received->value,
                'status' => TechnicalAssessmentStatus::Finished->value,
                'result' => null,
                'received_at' => '2026-08-15 09:00:00',
                'finished_at' => '2026-08-20 17:00:00',
            ],
        )
        ->assertHasFormErrors(['result' => 'required']);

    expect($jobApplication->technicalAssessments()->count())
        ->toBe(0);
});

test('it rejects a technical assessment finished before it was received', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Invalid Assessment Dates Company'),
        'job_title' => 'Backend Developer',
    ]);

    Livewire::test(TechnicalAssessmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->callAction(
            TestAction::make('create')->table(),
            data: [
                'test_type' => TechnicalAssessmentType::AtHome->value,
                'direction' => TechnicalAssessmentDirection::Received->value,
                'status' => TechnicalAssessmentStatus::Finished->value,
                'result' => 'passed',
                'received_at' => '2026-08-20 09:00:00',
                'finished_at' => '2026-08-19 17:00:00',
            ],
        )
        ->assertHasFormErrors(['finished_at']);

    expect($jobApplication->technicalAssessments()->count())
        ->toBe(0);
});

test('it can edit a technical assessment from the relation manager', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Edit Technical Assessment Company'),
        'job_title' => 'Backend Developer',
    ]);

    $assessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
        'received_at' => '2026-08-15 09:00:00',
        'deadline_at' => '2026-08-25 23:59:00',
        'notes' => 'Original assessment notes.',
    ]);

    Livewire::test(TechnicalAssessmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->callAction(
            TestAction::make('edit')->table($assessment),
            data: [
                'notes' => 'Updated assessment notes.',
            ],
        )
        ->assertHasNoFormErrors();

    $assessment->refresh();

    expect($assessment->notes)
        ->toBe('Updated assessment notes.')
        ->and($assessment->job_application_id)
        ->toBe($jobApplication->id)
        ->and($jobApplication->technicalAssessments()->count())
        ->toBe(1);
});

test('it can delete a technical assessment from the relation manager', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Delete Technical Assessment Company'),
        'job_title' => 'Backend Developer',
    ]);

    $assessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
        'received_at' => '2026-08-15 09:00:00',
    ]);

    Livewire::test(TechnicalAssessmentsRelationManager::class, [
        'ownerRecord' => $jobApplication,
        'pageClass' => EditJobApplication::class,
    ])
        ->assertCanSeeTableRecords([$assessment])
        ->callAction(
            TestAction::make('delete')->table($assessment),
        )
        ->assertHasNoActionErrors()
        ->assertCanNotSeeTableRecords([$assessment]);

    expect($jobApplication->technicalAssessments()->count())
        ->toBe(0)
        ->and($assessment->fresh())
        ->toBeNull();
});
