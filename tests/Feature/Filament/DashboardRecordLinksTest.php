<?php

use App\Enums\ApplicationStatus;
use App\Enums\InterviewResult;
use App\Enums\InterviewType;
use App\Filament\Resources\JobApplications\JobApplicationResource;
use App\Filament\Widgets\PendingNextStepsTable;
use App\Filament\Widgets\RecentApplicationsTable;
use App\Filament\Widgets\UpcomingInterviewsTable;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

afterEach(function (): void {
    Carbon::setTestNow();
});

beforeEach(function (): void {
    $user = new User([
        'name' => 'Dashboard Record Links Test User',
        'email' => 'dashboard-record-links@example.com',
        'password' => 'password',
    ]);

    $user->save();

    Livewire::actingAs($user);
});

test('pending next steps rows link to the application view', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Pending Next Steps Link Company'),
        'job_title' => 'Senior Developer',
        'status' => ApplicationStatus::Sent,
        'next_step' => 'Wait for response',
        'next_action_at' => '2026-09-25',
    ]);

    Livewire::test(PendingNextStepsTable::class)
        ->assertSee(
            JobApplicationResource::getUrl(
                'view',
                ['record' => $jobApplication],
            ),
            false,
        );
});

test('recent application rows link to the application view', function () {
    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Recent Applications Link Company'),
        'job_title' => 'Backend Developer',
        'status' => ApplicationStatus::Pending,
    ]);

    Livewire::test(RecentApplicationsTable::class)
        ->assertSee(
            JobApplicationResource::getUrl(
                'view',
                ['record' => $jobApplication],
            ),
            false,
        );
});

test('upcoming interview rows link to their application view', function () {
    Carbon::setTestNow('2026-09-21 10:00:00');

    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Upcoming Interview Link Company'),
        'job_title' => 'Frontend Developer',
        'status' => ApplicationStatus::Responded,
    ]);

    $jobApplication->interviews()->create([
        'interview_at' => '2026-09-22 10:00:00',
        'interview_type' => InterviewType::Hr,
        'result' => InterviewResult::Pending,
    ]);

    Livewire::test(UpcomingInterviewsTable::class)
        ->assertSee(
            JobApplicationResource::getUrl(
                'view',
                ['record' => $jobApplication],
            ),
            false,
        );
});
