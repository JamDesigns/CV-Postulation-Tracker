<?php

use App\Enums\ApplicationStatus;
use App\Models\JobApplication;
use App\Models\User;
use App\Notifications\ApplicationNextActionReminder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

afterEach(function () {
    Carbon::setTestNow();
});

test('it sends a reminder for an application with a next action due today', function () {
    Carbon::setTestNow('2026-09-05 10:00:00');

    Notification::fake();

    $user = User::factory()->create();

    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Backend Developer',
        'status' => ApplicationStatus::Sent,
        'next_step' => 'Follow up with recruiter',
        'next_action_at' => '2026-09-05',
    ]);

    artisan('applications:send-reminders')
        ->expectsOutput('Sent 1 application reminder(s).')
        ->assertExitCode(0);

    Notification::assertSentTo(
        $user,
        ApplicationNextActionReminder::class,
        fn (ApplicationNextActionReminder $notification): bool => $notification->jobApplication->is($jobApplication),
    );

    assertDatabaseHas('job_application_reminders', [
        'job_application_id' => $jobApplication->id,
        'action_date' => '2026-09-05',
    ]);
});

test('it does not send the same reminder twice for the same application and date', function () {
    Carbon::setTestNow('2026-09-05 10:00:00');

    Notification::fake();

    $user = User::factory()->create();

    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Backend Developer',
        'status' => ApplicationStatus::Sent,
        'next_step' => 'Follow up with recruiter',
        'next_action_at' => '2026-09-05',
    ]);

    artisan('applications:send-reminders')
        ->expectsOutput('Sent 1 application reminder(s).')
        ->assertExitCode(0);

    artisan('applications:send-reminders')
        ->expectsOutput('Sent 0 application reminder(s).')
        ->assertExitCode(0);

    Notification::assertSentToTimes(
        $user,
        ApplicationNextActionReminder::class,
        1,
    );

    assertDatabaseCount('job_application_reminders', 1);
});

test('it sends overdue reminders and ignores future next actions', function () {
    Carbon::setTestNow('2026-09-05 10:00:00');

    Notification::fake();

    $user = User::factory()->create();

    $overdueApplication = JobApplication::query()->create([
        'company_id' => companyId('Past Company'),
        'job_title' => 'Past Application',
        'status' => ApplicationStatus::Sent,
        'next_step' => 'Past action',
        'next_action_at' => '2026-09-04',
    ]);

    JobApplication::query()->create([
        'company_id' => companyId('Future Company'),
        'job_title' => 'Future Application',
        'status' => ApplicationStatus::Sent,
        'next_step' => 'Future action',
        'next_action_at' => '2026-09-06',
    ]);

    artisan('applications:send-reminders')
        ->expectsOutput('Sent 1 application reminder(s).')
        ->assertExitCode(0);

    Notification::assertSentToTimes(
        $user,
        ApplicationNextActionReminder::class,
        1,
    );

    assertDatabaseCount('job_application_reminders', 1);

    assertDatabaseHas('job_application_reminders', [
        'job_application_id' => $overdueApplication->id,
        'action_date' => '2026-09-04',
    ]);
});

test('it ignores applications without a meaningful next step', function () {
    Carbon::setTestNow('2026-09-05 10:00:00');

    Notification::fake();

    User::factory()->create();

    JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Backend Developer',
        'status' => ApplicationStatus::Sent,
        'next_step' => '',
        'next_action_at' => '2026-09-05',
    ]);

    artisan('applications:send-reminders')
        ->expectsOutput('Sent 0 application reminder(s).')
        ->assertExitCode(0);

    Notification::assertNothingSent();

    assertDatabaseCount('job_application_reminders', 0);
});

test('it can send a new reminder when the next action date changes', function () {
    Carbon::setTestNow('2026-09-05 10:00:00');

    Notification::fake();

    $user = User::factory()->create();

    $jobApplication = JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Backend Developer',
        'status' => ApplicationStatus::Sent,
        'next_step' => 'Follow up with recruiter',
        'next_action_at' => '2026-09-05',
    ]);

    artisan('applications:send-reminders')
        ->expectsOutput('Sent 1 application reminder(s).')
        ->assertExitCode(0);

    Carbon::setTestNow('2026-09-06 10:00:00');

    $jobApplication->next_action_at = '2026-09-06';
    $jobApplication->save();

    artisan('applications:send-reminders')
        ->expectsOutput('Sent 1 application reminder(s).')
        ->assertExitCode(0);

    Notification::assertSentToTimes(
        $user,
        ApplicationNextActionReminder::class,
        2,
    );

    assertDatabaseCount('job_application_reminders', 2);

    assertDatabaseHas('job_application_reminders', [
        'job_application_id' => $jobApplication->id,
        'action_date' => '2026-09-05',
    ]);

    assertDatabaseHas('job_application_reminders', [
        'job_application_id' => $jobApplication->id,
        'action_date' => '2026-09-06',
    ]);
});

test('it does not record a reminder when there are no users to notify', function () {
    Carbon::setTestNow('2026-09-05 10:00:00');

    Notification::fake();

    JobApplication::query()->create([
        'company_id' => companyId('Test Company'),
        'job_title' => 'Backend Developer',
        'status' => ApplicationStatus::Sent,
        'next_step' => 'Follow up with recruiter',
        'next_action_at' => '2026-09-05',
    ]);

    artisan('applications:send-reminders')
        ->expectsOutput('No users found. No reminders were sent.')
        ->assertExitCode(0);

    Notification::assertNothingSent();

    assertDatabaseCount('job_application_reminders', 0);
});
