<?php

namespace App\Console\Commands;

use App\Enums\ApplicationStatus;
use App\Models\JobApplication;
use App\Models\User;
use App\Notifications\ApplicationNextActionReminder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('applications:send-reminders')]
#[Description('Send reminders for job applications with a next action due today or overdue')]
class SendApplicationReminders extends Command
{
    public function handle(): int
    {
        $users = User::query()->get();

        if ($users->isEmpty()) {
            $this->warn('No users found. No reminders were sent.');

            return self::SUCCESS;
        }

        $jobApplications = JobApplication::query()
            ->with('company')
            ->whereDate('next_action_at', '<=', today(), 'and')
            ->whereNotNull('next_step', 'and')
            ->whereNotIn('status', [
                ApplicationStatus::Rejected->value,
                ApplicationStatus::Hired->value,
            ], 'and')
            ->orderBy('id', 'asc')
            ->get();

        $sent = 0;

        foreach ($jobApplications as $jobApplication) {
            if (blank($jobApplication->next_step)) {
                continue;
            }

            $wasSent = DB::transaction(function () use ($jobApplication, $users): bool {
                $now = now();

                $inserted = DB::table('job_application_reminders')->insertOrIgnore([
                    'job_application_id' => $jobApplication->getKey(),
                    'action_date' => $jobApplication->next_action_at->toDateString(),
                    'sent_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if ($inserted === 0) {
                    return false;
                }

                foreach ($users as $user) {
                    $user->notify(
                        new ApplicationNextActionReminder($jobApplication),
                    );
                }

                return true;
            });

            if ($wasSent) {
                $sent++;
            }
        }

        $this->info("Sent {$sent} application reminder(s).");

        return self::SUCCESS;
    }
}
