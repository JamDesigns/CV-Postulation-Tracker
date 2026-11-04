<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\InterviewModality;
use App\Enums\InterviewResult;
use App\Enums\InterviewType;
use App\Enums\JobApplicationEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    protected $fillable = [
        'job_application_id',
        'interview_at',
        'interview_type',
        'interview_modality',
        'people',
        'expected_questions',
        'strengths_to_defend',
        'risks_to_clarify',
        'result',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'interview_at' => 'datetime',
            'interview_type' => InterviewType::class,
            'interview_modality' => InterviewModality::class,
            'result' => InterviewResult::class,
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Interview $interview): void {
            $jobApplication = $interview->jobApplication;

            if ($jobApplication === null) {
                return;
            }

            $jobApplication->events()->create([
                'type' => JobApplicationEventType::InterviewScheduled,
                'occurred_at' => $interview->created_at,
                'title' => __('job-application-events.types.interview_scheduled'),
                'body' => $interview->interview_at?->format('d/m/Y H:i'),
            ]);

            if (in_array($jobApplication->status, [
                ApplicationStatus::Rejected,
                ApplicationStatus::Hired,
                ApplicationStatus::TechnicalTest,
            ], true)) {
                return;
            }

            if (! in_array($interview->result, [
                InterviewResult::Pending,
                InterviewResult::WaitingFeedback,
                InterviewResult::Rejected,
            ], true)) {
                return;
            }

            $hasNewerEvent = $jobApplication->events()
                ->where('occurred_at', '>', $interview->interview_at)
                ->exists();

            $hasNewerInterview = $jobApplication->interviews()
                ->where('id', '!=', $interview->getKey())
                ->where('interview_at', '>', $interview->interview_at)
                ->exists();

            $hasNewerAssessment = $jobApplication->technicalAssessments()
                ->where(function ($query) use ($interview): void {
                    $query->where('received_at', '>', $interview->interview_at)
                        ->orWhere(function ($query) use ($interview): void {
                            $query->whereNull('received_at')
                                ->where('created_at', '>', $interview->interview_at);
                        });
                })
                ->exists();

            if ($hasNewerEvent || $hasNewerInterview || $hasNewerAssessment) {
                return;
            }

            $status = match ($interview->result) {
                InterviewResult::Rejected => ApplicationStatus::Rejected,
                InterviewResult::WaitingFeedback => ApplicationStatus::WaitingFeedback,
                default => ApplicationStatus::Interview,
            };

            $jobApplication->forceFill([
                'status' => $status,
                'next_step' => $status->nextStep(),
                'next_action_at' => $status === ApplicationStatus::Interview
                    ? $interview->interview_at?->toDateString()
                    : null,
            ])->save();
        });

        static::updated(function (Interview $interview): void {
            if (
                ! $interview->wasChanged('result')
                || ! in_array($interview->result, [
                    InterviewResult::WaitingFeedback,
                    InterviewResult::Rejected,
                ], true)
            ) {
                return;
            }

            $jobApplication = $interview->jobApplication;

            if ($jobApplication === null) {
                return;
            }

            if (in_array($jobApplication->status, [
                ApplicationStatus::Rejected,
                ApplicationStatus::Hired,
                ApplicationStatus::TechnicalTest,
            ], true)) {
                return;
            }

            $hasNewerEvent = $jobApplication->events()
                ->where('occurred_at', '>', $interview->interview_at)
                ->exists();

            $hasNewerInterview = $jobApplication->interviews()
                ->where('id', '!=', $interview->getKey())
                ->where('interview_at', '>', $interview->interview_at)
                ->exists();

            $hasNewerAssessment = $jobApplication->technicalAssessments()
                ->where(function ($query) use ($interview): void {
                    $query->where('received_at', '>', $interview->interview_at)
                        ->orWhere(function ($query) use ($interview): void {
                            $query->whereNull('received_at')
                                ->where('created_at', '>', $interview->interview_at);
                        });
                })
                ->exists();

            if ($hasNewerEvent || $hasNewerInterview || $hasNewerAssessment) {
                return;
            }

            $status = $interview->result === InterviewResult::Rejected
                ? ApplicationStatus::Rejected
                : ApplicationStatus::WaitingFeedback;

            $jobApplication->forceFill([
                'status' => $status,
                'next_step' => $status->nextStep(),
                'next_action_at' => null,
            ])->save();
        });

    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }
}
