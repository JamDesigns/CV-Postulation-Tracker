<?php

use App\Enums\ApplicationStatus;
use App\Enums\InterviewType;
use App\Models\JobApplication;

test('it synchronizes the application when an interview is created', function () {
    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Responded,
        'next_step' => 'Review response',
        'next_action_at' => '2026-08-18',
    ]);

    $jobApplication->interviews()->create([
        'interview_at' => '2026-08-22 10:30:00',
        'interview_type' => InterviewType::Hr,
    ]);

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::Interview)
        ->and($jobApplication->next_step)
        ->toBe(ApplicationStatus::Interview->nextStep())
        ->and($jobApplication->next_action_at->toDateString())
        ->toBe('2026-08-22');
});

test('it does not synchronize the application when an historical interview is edited', function () {
    $jobApplication = JobApplication::query()->create([
        'company_name' => 'Test Company',
        'job_title' => 'Full Stack Developer',
        'status' => ApplicationStatus::Responded,
    ]);

    $interview = $jobApplication->interviews()->create([
        'interview_at' => '2026-08-22 10:30:00',
        'interview_type' => InterviewType::Technical,
    ]);

    $jobApplication->forceFill([
        'status' => ApplicationStatus::TechnicalTest,
        'next_step' => 'Complete technical test',
        'next_action_at' => '2026-08-25',
    ])->save();

    $interview->forceFill([
        'interview_at' => '2026-08-28 12:00:00',
    ])->save();

    $jobApplication->refresh();

    expect($jobApplication->status)
        ->toBe(ApplicationStatus::TechnicalTest)
        ->and($jobApplication->next_step)
        ->toBe('Complete technical test')
        ->and($jobApplication->next_action_at->toDateString())
        ->toBe('2026-08-25');
});
