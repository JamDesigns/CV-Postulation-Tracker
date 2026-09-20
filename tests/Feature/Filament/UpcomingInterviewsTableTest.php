<?php

use App\Enums\ApplicationStatus;
use App\Enums\InterviewResult;
use App\Enums\InterviewType;
use App\Filament\Widgets\UpcomingInterviewsTable;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

afterEach(function (): void {
    Carbon::setTestNow();
});

test('it only shows future interviews that are still pending', function () {
    Carbon::setTestNow('2026-09-20 10:00:00');

    $user = new User([
        'name' => 'Upcoming Interviews Test User',
        'email' => 'upcoming-interviews@example.com',
        'password' => 'password',
    ]);

    $user->save();

    Livewire::actingAs($user);

    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Upcoming Interviews Test Company'),
        'job_title' => 'Senior Developer',
        'status' => ApplicationStatus::Responded,
    ]);

    $pendingInterview = $jobApplication->interviews()->create([
        'interview_at' => '2026-09-22 10:00:00',
        'interview_type' => InterviewType::Hr,
        'result' => InterviewResult::Pending,
    ]);

    $waitingFeedbackInterview = $jobApplication->interviews()->create([
        'interview_at' => '2026-09-23 10:00:00',
        'interview_type' => InterviewType::Technical,
        'result' => InterviewResult::WaitingFeedback,
    ]);

    $pastPendingInterview = $jobApplication->interviews()->create([
        'interview_at' => '2026-09-19 10:00:00',
        'interview_type' => InterviewType::Hr,
        'result' => InterviewResult::Pending,
    ]);

    Livewire::test(UpcomingInterviewsTable::class)
        ->assertCanSeeTableRecords([
            $pendingInterview,
        ])
        ->assertCanNotSeeTableRecords([
            $waitingFeedbackInterview,
            $pastPendingInterview,
        ]);
});
