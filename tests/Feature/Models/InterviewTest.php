<?php

use App\Enums\ApplicationStatus;
use App\Enums\InterviewResult;
use App\Enums\InterviewType;
use App\Enums\JobApplicationEventType;
use App\Models\JobApplication;
use Illuminate\Support\Carbon;

beforeEach(function () {
    Carbon::setTestNow('2026-11-01 10:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

test('it synchronizes the application when an interview is created', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Responded,
        'next_step' => 'Review response',
        'next_action_at' => '2026-11-02',
    ]);

    $interview = $jobApplication->interviews()->create([
        'interview_at' => '2026-11-06 10:30:00',
        'interview_type' => InterviewType::Hr,
        'result' => InterviewResult::Pending,
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::Interview)
        ->and($jobApplication->next_step)
        ->toBe(ApplicationStatus::Interview->nextStep())
        ->and($jobApplication->next_action_at->toDateString())
        ->toBe('2026-11-06');

    $event = $jobApplication->events()
        ->where('type', JobApplicationEventType::InterviewScheduled->value)
        ->first();

    expect($event)->not->toBeNull()
        ->and($event->occurred_at->toDateTimeString())
        ->toBe($interview->created_at->toDateTimeString())
        ->and($event->body)
        ->toBe('06/11/2026 10:30');
});

test('it rejects the application when an interview is created as rejected', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Rejected Interview Company'),
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Responded,
        'next_step' => 'Review response',
        'next_action_at' => '2026-11-02',
    ]);

    $jobApplication->interviews()->create([
        'interview_at' => '2026-11-06 10:30:00',
        'interview_type' => InterviewType::Hr,
        'result' => InterviewResult::Rejected,
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::Rejected)
        ->and($jobApplication->next_step)
        ->toBeNull()
        ->and($jobApplication->next_action_at)
        ->toBeNull();
});

test('it preserves the application state when an interview is created as cancelled', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Cancelled Interview Company'),
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Responded,
        'next_step' => 'Review response',
        'next_action_at' => '2026-11-02',
    ]);

    $jobApplication->interviews()->create([
        'interview_at' => '2026-11-06 10:30:00',
        'interview_type' => InterviewType::Hr,
        'result' => InterviewResult::Cancelled,
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::Responded)
        ->and($jobApplication->next_step)
        ->toBe('Review response')
        ->and($jobApplication->next_action_at->toDateString())
        ->toBe('2026-11-02');
});

test('it does not synchronize the application when an historical interview is edited', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Responded,
    ]);

    $interview = $jobApplication->interviews()->create([
        'interview_at' => '2026-11-06 10:30:00',
        'interview_type' => InterviewType::Technical,
    ]);

    $jobApplication->forceFill([
        'status' => ApplicationStatus::TechnicalTest,
        'next_step' => 'Complete technical test',
        'next_action_at' => '2026-11-09',
    ])->save();

    $interview->forceFill([
        'interview_at' => '2026-11-10 12:00:00',
    ])->save();

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::TechnicalTest)
        ->and($jobApplication->next_step)
        ->toBe('Complete technical test')
        ->and($jobApplication->next_action_at->toDateString())
        ->toBe('2026-11-09');
});

test('it does not synchronize the application when an older historical interview is created', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Historical Interview Company'),
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Responded,
    ]);

    $jobApplication->events()->create([
        'type' => JobApplicationEventType::TechnicalTest,
        'occurred_at' => '2026-11-04 10:00:00',
        'title' => 'Technical test assigned',
        'status_to' => ApplicationStatus::TechnicalTest,
        'next_action_at' => '2026-11-09',
    ]);

    $jobApplication->interviews()->create([
        'interview_at' => '2026-11-03 10:30:00',
        'interview_type' => InterviewType::Hr,
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::TechnicalTest)
        ->and($jobApplication->next_step)
        ->toBe(ApplicationStatus::TechnicalTest->nextStep())
        ->and($jobApplication->next_action_at?->toDateString())
        ->toBe('2026-11-09');
});

test('it does not synchronize the application when a newer interview already exists', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Multiple Interviews Company'),
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Responded,
    ]);

    $jobApplication->interviews()->create([
        'interview_at' => '2026-11-08 10:30:00',
        'interview_type' => InterviewType::Technical,
        'result' => InterviewResult::Pending,
    ]);

    $jobApplication->interviews()->create([
        'interview_at' => '2026-11-05 10:30:00',
        'interview_type' => InterviewType::Hr,
        'result' => InterviewResult::Pending,
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::Interview)
        ->and($jobApplication->next_step)
        ->toBe(ApplicationStatus::Interview->nextStep())
        ->and($jobApplication->next_action_at?->toDateString())
        ->toBe('2026-11-08');
});

test('it synchronizes supported interview results', function (
    InterviewResult $result,
    ApplicationStatus $expectedStatus,
) {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Non Final Interview Company'),
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Responded,
    ]);

    $jobApplication->interviews()->create([
        'interview_at' => '2026-11-06 10:30:00',
        'interview_type' => InterviewType::Hr,
        'result' => $result,
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe($expectedStatus)
        ->and($jobApplication->next_step)
        ->toBe($expectedStatus->nextStep())
        ->and($jobApplication->next_action_at?->toDateString())
        ->toBe($expectedStatus === ApplicationStatus::Interview ? '2026-11-06' : null);
})->with([
    'pending' => [InterviewResult::Pending, ApplicationStatus::Interview],
    'waiting feedback' => [InterviewResult::WaitingFeedback, ApplicationStatus::WaitingFeedback],
]);
