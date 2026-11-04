<?php

use App\Enums\ApplicationStatus;
use App\Enums\TechnicalAssessmentDirection;
use App\Enums\TechnicalAssessmentStatus;
use App\Enums\TechnicalAssessmentType;
use App\Models\JobApplication;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    Carbon::setTestNow('2026-08-01 09:00:00');
});

afterEach(function (): void {
    Carbon::setTestNow();
});

test('it synchronizes the application when a technical assessment is created', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Technical Assessment Company'),
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Responded,
        'next_step' => 'Review response',
        'next_action_at' => '2026-08-18',
    ]);

    $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
        'received_at' => '2026-08-15 09:00:00',
        'deadline_at' => '2026-08-25 23:59:00',
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::TechnicalTest)
        ->and($jobApplication->next_step)
        ->toBe(ApplicationStatus::TechnicalTest->nextStep())
        ->and($jobApplication->next_action_at?->toDateString())
        ->toBe('2026-08-25');
});

test('it sets the application to waiting feedback when a finished assessment is created', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Finished Assessment Company'),
        'job_title' => 'Backend Developer',
        'status' => ApplicationStatus::TechnicalTest,
        'next_step' => 'Complete technical assessment',
        'next_action_at' => '2026-08-25',
    ]);

    $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Finished,
        'received_at' => '2026-08-15 09:00:00',
        'finished_at' => '2026-08-20 17:00:00',
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::WaitingFeedback)
        ->and($jobApplication->next_step)
        ->toBe(ApplicationStatus::WaitingFeedback->nextStep())
        ->and($jobApplication->next_action_at)
        ->toBeNull();
});

test('it preserves the application state when a cancelled assessment is created', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Cancelled Assessment Company'),
        'job_title' => 'Backend Developer',
        'status' => ApplicationStatus::Responded,
        'next_step' => 'Review response',
        'next_action_at' => '2026-08-18',
    ]);

    $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Cancelled,
        'received_at' => '2026-08-15 09:00:00',
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::Responded)
        ->and($jobApplication->next_step)
        ->toBe('Review response')
        ->and($jobApplication->next_action_at?->toDateString())
        ->toBe('2026-08-18');
});

test('it preserves final application statuses when a technical assessment is created', function (ApplicationStatus $status) {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Final Status Assessment Company'),
        'job_title' => 'Backend Developer',
        'status' => $status,
    ]);

    $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
        'received_at' => '2026-08-15 09:00:00',
        'deadline_at' => '2026-08-25 23:59:00',
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe($status)
        ->and($jobApplication->next_step)
        ->toBeNull()
        ->and($jobApplication->next_action_at)
        ->toBeNull();
})->with([
    'rejected' => ApplicationStatus::Rejected,
    'hired' => ApplicationStatus::Hired,
]);

test('it sets the application to waiting feedback when an assessment is finished', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Updated Assessment Company'),
        'job_title' => 'Backend Developer',
        'status' => ApplicationStatus::Responded,
    ]);

    $assessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
        'received_at' => '2026-08-15 09:00:00',
        'deadline_at' => '2026-08-25 23:59:00',
    ]);

    expect($jobApplication->fresh()->status)
        ->toBe(ApplicationStatus::TechnicalTest);

    $assessment->update([
        'status' => TechnicalAssessmentStatus::Finished,
        'finished_at' => '2026-08-20 17:00:00',
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::WaitingFeedback)
        ->and($jobApplication->next_step)
        ->toBe(ApplicationStatus::WaitingFeedback->nextStep())
        ->and($jobApplication->next_action_at)
        ->toBeNull();
});

test('it does not override the application when a newer interview exists', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Assessment With Newer Interview Company'),
        'job_title' => 'Backend Developer',
        'status' => ApplicationStatus::Responded,
    ]);

    $jobApplication->interviews()->create([
        'interview_at' => '2026-08-20 10:00:00',
    ]);

    $statusBeforeAssessment = $jobApplication->fresh()->status;

    $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
        'received_at' => '2026-08-15 09:00:00',
        'deadline_at' => '2026-08-25 23:59:00',
    ]);

    expect($jobApplication->fresh()->status)
        ->toBe($statusBeforeAssessment);
});

test('it does not override the application when a newer event exists', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Assessment With Newer Event Company'),
        'job_title' => 'Backend Developer',
        'status' => ApplicationStatus::Responded,
    ]);

    $jobApplication->events()->create([
        'title' => 'Follow-up after technical assessment',
        'occurred_at' => '2026-08-20 10:00:00',
    ]);

    $statusBeforeAssessment = $jobApplication->fresh()->status;

    $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
        'received_at' => '2026-08-15 09:00:00',
        'deadline_at' => '2026-08-25 23:59:00',
    ]);

    expect($jobApplication->fresh()->status)
        ->toBe($statusBeforeAssessment);
});

test('it does not override the application when a newer technical assessment exists', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Multiple Assessments Company'),
        'job_title' => 'Backend Developer',
        'status' => ApplicationStatus::Responded,
    ]);

    $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
        'received_at' => '2026-08-20 09:00:00',
        'deadline_at' => '2026-08-30 23:59:00',
    ]);

    $statusBeforeOlderAssessment = $jobApplication->fresh()->status;
    $nextActionBeforeOlderAssessment = $jobApplication->fresh()->next_action_at?->toDateString();

    $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
        'received_at' => '2026-08-15 09:00:00',
        'deadline_at' => '2026-08-25 23:59:00',
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe($statusBeforeOlderAssessment)
        ->and($jobApplication->next_action_at?->toDateString())
        ->toBe($nextActionBeforeOlderAssessment);
});

test('it does not override the application when an older assessment is finished', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Older Finished Assessment Company'),
        'job_title' => 'Backend Developer',
        'status' => ApplicationStatus::Responded,
    ]);

    $olderAssessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
        'received_at' => '2026-08-15 09:00:00',
        'deadline_at' => '2026-08-25 23:59:00',
    ]);

    $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
        'received_at' => '2026-08-20 09:00:00',
        'deadline_at' => '2026-08-30 23:59:00',
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::TechnicalTest);

    $nextStepBeforeUpdate = $jobApplication->next_step;
    $nextActionBeforeUpdate = $jobApplication->next_action_at?->toDateString();

    $olderAssessment->update([
        'status' => TechnicalAssessmentStatus::Finished,
        'finished_at' => '2026-08-18 17:00:00',
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::TechnicalTest)
        ->and($jobApplication->next_step)
        ->toBe($nextStepBeforeUpdate)
        ->and($jobApplication->next_action_at?->toDateString())
        ->toBe($nextActionBeforeUpdate);
});

test('it does not override the application when an assessment is finished after a newer interview', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Finished Assessment With Newer Interview Company'),
        'job_title' => 'Backend Developer',
        'status' => ApplicationStatus::Responded,
    ]);

    $assessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
        'received_at' => '2026-08-15 09:00:00',
        'deadline_at' => '2026-08-25 23:59:00',
    ]);

    $jobApplication->interviews()->create([
        'interview_at' => '2026-08-20 10:00:00',
    ]);

    $statusBeforeUpdate = $jobApplication->fresh()->status;

    $assessment->update([
        'status' => TechnicalAssessmentStatus::Finished,
        'finished_at' => '2026-08-18 17:00:00',
    ]);

    expect($jobApplication->fresh()->status)
        ->toBe($statusBeforeUpdate);
});

test('it does not override the application when an assessment is finished after a newer event', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Finished Assessment With Newer Event Company'),
        'job_title' => 'Backend Developer',
        'status' => ApplicationStatus::Responded,
    ]);

    $assessment = $jobApplication->technicalAssessments()->create([
        'test_type' => TechnicalAssessmentType::AtHome,
        'direction' => TechnicalAssessmentDirection::Received,
        'status' => TechnicalAssessmentStatus::Pending,
        'received_at' => '2026-08-15 09:00:00',
        'deadline_at' => '2026-08-25 23:59:00',
    ]);

    $jobApplication->events()->create([
        'title' => 'Follow-up after technical assessment',
        'occurred_at' => '2026-08-20 10:00:00',
    ]);

    $jobApplication->refresh();

    $statusBeforeUpdate = $jobApplication->status;
    $nextStepBeforeUpdate = $jobApplication->next_step;
    $nextActionBeforeUpdate = $jobApplication->next_action_at?->toDateString();

    $assessment->update([
        'status' => TechnicalAssessmentStatus::Finished,
        'finished_at' => '2026-08-18 17:00:00',
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe($statusBeforeUpdate)
        ->and($jobApplication->next_step)
        ->toBe($nextStepBeforeUpdate)
        ->and($jobApplication->next_action_at?->toDateString())
        ->toBe($nextActionBeforeUpdate);
});
