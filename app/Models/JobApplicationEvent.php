<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\InterviewResult;
use App\Enums\JobApplicationEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplicationEvent extends Model
{
    protected $fillable = [
        'job_application_id',
        'type',
        'occurred_at',
        'title',
        'body',
        'status_from',
        'status_to',
        'next_action_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => JobApplicationEventType::class,
            'occurred_at' => 'datetime',
            'status_from' => ApplicationStatus::class,
            'status_to' => ApplicationStatus::class,
            'next_action_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (JobApplicationEvent $event): void {
            $jobApplication = $event->jobApplication;

            if ($jobApplication === null) {
                return;
            }

            $hasNewerEvent = $jobApplication->events()
                ->where('occurred_at', '>', $event->occurred_at)
                ->exists();

            $hasNewerInterview = $jobApplication->interviews()
                ->where('interview_at', '>', $event->occurred_at)
                ->exists();

            if ($hasNewerEvent || $hasNewerInterview) {
                if ($event->status_from === null) {
                    $previousEvent = $jobApplication->events()
                        ->where('occurred_at', '<', $event->occurred_at)
                        ->whereNotNull('status_to')
                        ->latest('occurred_at')
                        ->first();

                    $previousInterview = $jobApplication->interviews()
                        ->where('interview_at', '<', $event->occurred_at)
                        ->where('result', '<>', InterviewResult::Cancelled->value)
                        ->latest('interview_at')
                        ->first();

                    if (
                        $previousInterview !== null
                        && (
                            $previousEvent === null
                            || $previousInterview->interview_at->gt($previousEvent->occurred_at)
                        )
                    ) {
                        $event->status_from = $previousInterview->result === InterviewResult::Rejected
                            ? ApplicationStatus::Rejected
                            : ApplicationStatus::Interview;
                    } else {
                        $event->status_from = $previousEvent?->status_to;
                    }
                }

                return;
            }

            if ($event->status_from === null) {
                $event->status_from = $jobApplication->status;
            }
        });

        static::created(function (JobApplicationEvent $event): void {
            $jobApplication = $event->jobApplication;

            if ($jobApplication === null) {
                return;
            }

            $hasNewerEvent = $jobApplication->events()
                ->where('id', '!=', $event->getKey())
                ->where('occurred_at', '>', $event->occurred_at)
                ->exists();

            $hasNewerInterview = $jobApplication->interviews()
                ->where('interview_at', '>', $event->occurred_at)
                ->exists();

            if ($hasNewerEvent || $hasNewerInterview) {
                return;
            }

            $attributes = [
                'next_action_at' => $event->next_action_at,
            ];

            if ($event->status_to instanceof ApplicationStatus) {
                $attributes['status'] = $event->status_to;
                $attributes['next_step'] = $event->status_to->nextStep();

                if (
                    $event->status_to === ApplicationStatus::Sent
                    && $jobApplication->sent_at === null
                ) {
                    $attributes['sent_at'] = $event->occurred_at->toDateString();
                }

                if (in_array($event->status_to, [
                    ApplicationStatus::Rejected,
                    ApplicationStatus::Hired,
                ], true)) {
                    $attributes['next_action_at'] = null;
                }
            }

            $jobApplication->forceFill($attributes)->save();
        });
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }
}
