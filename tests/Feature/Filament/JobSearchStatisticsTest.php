<?php

use App\Enums\ApplicationStatus;
use App\Enums\InterviewType;
use App\Enums\JobApplicationEventType;
use App\Filament\Pages\JobSearchStatistics;
use App\Models\JobApplication;
use Illuminate\Support\Carbon;

afterEach(function (): void {
    Carbon::setTestNow();
});

test('it resolves the start date for each statistics period', function () {
    Carbon::setTestNow('2026-09-12 10:00:00');

    $page = new JobSearchStatistics;

    $page->period = 'all';

    expect($page->periodStart())
        ->toBeNull();

    $page->period = 'year';

    expect($page->periodStart()?->toDateString())
        ->toBe('2026-01-01');

    $page->period = 'last_6_months';

    expect($page->periodStart()?->toDateString())
        ->toBe('2026-04-01');

    $page->period = 'last_3_months';

    expect($page->periodStart()?->toDateString())
        ->toBe('2026-07-01');

    $page->period = 'month';

    expect($page->periodStart()?->toDateString())
        ->toBe('2026-09-01');
});

test('it calculates statistics percentages safely', function () {
    $page = new JobSearchStatistics;

    expect($page->percentage(2, 11))
        ->toBe(18.2)
        ->and($page->percentage(0, 11))
        ->toBe(0.0)
        ->and($page->percentage(5, 0))
        ->toBe(0.0);
});

test('it calculates statistics for applications sent in the selected period', function () {
    Carbon::setTestNow('2026-09-12 10:00:00');

    $companyId = companyId('Statistics Company');

    $createApplication = fn (
        string $title,
        ApplicationStatus $status,
        string $sentAt,
    ): JobApplication => JobApplication::query()->create([
        'company_id' => $companyId,
        'job_title' => $title,
        'status' => $status,
        'sent_at' => $sentAt,
    ]);

    $createApplication(
        'Sent application',
        ApplicationStatus::Sent,
        '2026-09-01',
    );

    $createApplication(
        'Responded application',
        ApplicationStatus::Responded,
        '2026-09-02',
    );

    $interviewApplication = $createApplication(
        'Interview application',
        ApplicationStatus::Responded,
        '2026-09-03',
    );

    $interviewApplication->events()->create([
        'type' => JobApplicationEventType::ResponseReceived,
        'occurred_at' => '2026-09-04 09:00:00',
        'title' => 'Response received',
    ]);

    $interviewApplication->interviews()->create([
        'interview_at' => '2026-09-08 10:00:00',
        'interview_type' => InterviewType::Hr,
    ]);

    $technicalTestApplication = $createApplication(
        'Technical test application',
        ApplicationStatus::TechnicalTest,
        '2026-09-04',
    );

    $technicalTestApplication->events()->create([
        'type' => JobApplicationEventType::ResponseReceived,
        'occurred_at' => '2026-09-05 09:00:00',
        'title' => 'Response received',
    ]);

    $rejectedApplication = $createApplication(
        'Rejected application',
        ApplicationStatus::Rejected,
        '2026-09-05',
    );

    $rejectedApplication->events()->create([
        'type' => JobApplicationEventType::ResponseReceived,
        'occurred_at' => '2026-09-06 09:00:00',
        'title' => 'Response received',
    ]);

    $hiredApplication = $createApplication(
        'Hired application',
        ApplicationStatus::Hired,
        '2026-09-06',
    );

    $hiredApplication->events()->create([
        'type' => JobApplicationEventType::ResponseReceived,
        'occurred_at' => '2026-09-07 09:00:00',
        'title' => 'Response received',
    ]);

    $createApplication(
        'Previous month application',
        ApplicationStatus::Sent,
        '2026-08-31',
    );

    JobApplication::query()->create([
        'company_id' => $companyId,
        'job_title' => 'Unsent draft application',
        'status' => ApplicationStatus::Responded,
        'sent_at' => null,
    ]);

    $page = new JobSearchStatistics;
    $page->period = 'month';

    expect($page->statistics())->toBe([
        'sent' => 6,
        'responses' => 5,
        'interviews' => 1,
        'technical_tests' => 1,
        'rejected' => 1,
        'hired' => 1,
    ]);
});
