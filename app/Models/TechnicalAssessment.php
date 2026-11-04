<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\TechnicalAssessmentDirection;
use App\Enums\TechnicalAssessmentModality;
use App\Enums\TechnicalAssessmentResult;
use App\Enums\TechnicalAssessmentStatus;
use App\Enums\TechnicalAssessmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TechnicalAssessment extends Model
{
    protected $fillable = [
        'job_application_id',
        'test_type',
        'modality',
        'direction',
        'status',
        'result',
        'received_at',
        'scheduled_at',
        'deadline_at',
        'finished_at',
        'result_communicated_at',
        'source_url',
        'delivery_url',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'test_type' => TechnicalAssessmentType::class,
            'modality' => TechnicalAssessmentModality::class,
            'direction' => TechnicalAssessmentDirection::class,
            'status' => TechnicalAssessmentStatus::class,
            'result' => TechnicalAssessmentResult::class,
            'received_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'deadline_at' => 'datetime',
            'finished_at' => 'datetime',
            'result_communicated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (TechnicalAssessment $assessment): void {
            $jobApplication = $assessment->jobApplication;

            if ($jobApplication === null) {
                return;
            }

            if (in_array($jobApplication->status, [
                ApplicationStatus::Rejected,
                ApplicationStatus::Hired,
            ], true)) {
                return;
            }

            if ($assessment->status === TechnicalAssessmentStatus::Cancelled) {
                return;
            }

            $occurredAt = $assessment->received_at
                ?? $assessment->finished_at
                ?? $assessment->scheduled_at
                ?? $assessment->created_at;

            $hasNewerEvent = $jobApplication->events()
                ->where('occurred_at', '>', $occurredAt)
                ->exists();

            $hasNewerInterview = $jobApplication->interviews()
                ->where('interview_at', '>', $occurredAt)
                ->exists();

            $hasNewerAssessment = $jobApplication->technicalAssessments()
                ->where('id', '!=', $assessment->getKey())
                ->where(function ($query) use ($occurredAt): void {
                    $query->where('received_at', '>', $occurredAt)
                        ->orWhere(function ($query) use ($occurredAt): void {
                            $query->whereNull('received_at')
                                ->where('created_at', '>', $occurredAt);
                        });
                })
                ->exists();

            if ($hasNewerEvent || $hasNewerInterview || $hasNewerAssessment) {
                return;
            }

            $status = $assessment->status === TechnicalAssessmentStatus::Finished
                ? ApplicationStatus::WaitingFeedback
                : ApplicationStatus::TechnicalTest;

            $nextActionAt = $status === ApplicationStatus::TechnicalTest
                ? ($assessment->scheduled_at ?? $assessment->deadline_at)?->toDateString()
                : null;

            $jobApplication->forceFill([
                'status' => $status,
                'next_step' => $status->nextStep(),
                'next_action_at' => $nextActionAt,
            ])->save();
        });

        static::updated(function (TechnicalAssessment $assessment): void {
            if (
                ! $assessment->wasChanged('status')
                || $assessment->status !== TechnicalAssessmentStatus::Finished
            ) {
                return;
            }

            $jobApplication = $assessment->jobApplication;

            if (
                $jobApplication === null
                || $jobApplication->status !== ApplicationStatus::TechnicalTest
            ) {
                return;
            }

            $occurredAt = $assessment->finished_at ?? now();

            $hasNewerEvent = $jobApplication->events()
                ->where('occurred_at', '>', $occurredAt)
                ->exists();

            $hasNewerInterview = $jobApplication->interviews()
                ->where('interview_at', '>', $occurredAt)
                ->exists();

            $hasNewerAssessment = $jobApplication->technicalAssessments()
                ->where('id', '!=', $assessment->getKey())
                ->where(function ($query) use ($occurredAt): void {
                    $query->where('received_at', '>', $occurredAt)
                        ->orWhere(function ($query) use ($occurredAt): void {
                            $query->whereNull('received_at')
                                ->where('created_at', '>', $occurredAt);
                        });
                })
                ->exists();

            if ($hasNewerEvent || $hasNewerInterview || $hasNewerAssessment) {
                return;
            }

            $status = ApplicationStatus::WaitingFeedback;

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

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}
