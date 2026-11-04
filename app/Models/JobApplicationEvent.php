<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\CommunicationChannel;
use App\Enums\InterviewResult;
use App\Enums\JobApplicationEventType;
use App\Enums\TechnicalAssessmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class JobApplicationEvent extends Model
{
    protected $fillable = [
        'job_application_id',
        'contact_id',
        'type',
        'occurred_at',
        'title',
        'body',
        'status_from',
        'status_to',
        'next_action_at',
        'communication_channel',
    ];

    protected function casts(): array
    {
        return [
            'type' => JobApplicationEventType::class,
            'occurred_at' => 'datetime',
            'status_from' => ApplicationStatus::class,
            'status_to' => ApplicationStatus::class,
            'next_action_at' => 'date',
            'communication_channel' => CommunicationChannel::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (JobApplicationEvent $event): void {
            $jobApplication = $event->jobApplication;

            if (
                $jobApplication !== null
                && $jobApplication->created_at !== null
                && $event->occurred_at !== null
                && $event->occurred_at->startOfDay()->lt(
                    $jobApplication->created_at->startOfDay()
                )
            ) {
                throw ValidationException::withMessages([
                    'occurred_at' => __('job-application-events.validation.occurred_at_min_date'),
                ]);
            }

            if (blank($event->contact_id)) {
                return;
            }

            $shouldValidateContact = ! $event->exists
                || $event->isDirty('contact_id')
                || $event->isDirty('job_application_id');

            if (! $shouldValidateContact) {
                return;
            }

            if (blank($event->job_application_id)) {
                return;
            }

            $contactIsAttached = JobApplicationContact::query()
                ->where('job_application_id', $event->job_application_id)
                ->where('contact_id', $event->contact_id)
                ->exists();

            if ($contactIsAttached) {
                return;
            }

            throw ValidationException::withMessages([
                'contact_id' => __('contacts.validation.not_attached_to_application'),
            ]);
        });

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

            $hasNewerAssessment = $jobApplication->technicalAssessments()
                ->where(function ($query) use ($event): void {
                    $query->where('received_at', '>', $event->occurred_at)
                        ->orWhere(function ($query) use ($event): void {
                            $query->whereNull('received_at')
                                ->where('created_at', '>', $event->occurred_at);
                        })
                        ->orWhere(function ($query) use ($event): void {
                            $query->where('status', TechnicalAssessmentStatus::Finished->value)
                                ->whereNotNull('finished_at')
                                ->where('finished_at', '>', $event->occurred_at);
                        });
                })
                ->exists();

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

                $previousAssessment = $jobApplication->technicalAssessments()
                    ->where('status', '<>', TechnicalAssessmentStatus::Cancelled->value)
                    ->where(function ($query) use ($event): void {
                        $query->where('received_at', '<', $event->occurred_at)
                            ->orWhere(function ($query) use ($event): void {
                                $query->whereNull('received_at')
                                    ->where('created_at', '<', $event->occurred_at);
                            });
                    })
                    ->get()
                    ->flatMap(function (TechnicalAssessment $assessment): array {
                        $startedAt = $assessment->received_at ?? $assessment->created_at;

                        $transitions = [
                            [
                                'date' => $startedAt,
                                'status' => ApplicationStatus::TechnicalTest,
                            ],
                        ];

                        if (
                            $assessment->status === TechnicalAssessmentStatus::Finished
                            && $assessment->finished_at !== null
                        ) {
                            $transitions[] = [
                                'date' => $assessment->finished_at,
                                'status' => ApplicationStatus::WaitingFeedback,
                            ];
                        }

                        return $transitions;
                    })
                    ->filter(
                        fn (array $transition): bool => $transition['date']->lt($event->occurred_at)
                    )
                    ->sortByDesc('date')
                    ->first();

                $transitions = collect();

                if ($previousEvent !== null) {
                    $transitions->push([
                        'date' => $previousEvent->occurred_at,
                        'status' => $previousEvent->status_to,
                    ]);
                }

                if ($previousInterview !== null) {
                    $transitions->push([
                        'date' => $previousInterview->interview_at,
                        'status' => match ($previousInterview->result) {
                            InterviewResult::Rejected => ApplicationStatus::Rejected,
                            InterviewResult::WaitingFeedback => ApplicationStatus::WaitingFeedback,
                            default => ApplicationStatus::Interview,
                        },
                    ]);
                }

                if ($previousAssessment !== null) {
                    $transitions->push($previousAssessment);
                }

                $event->status_from = $transitions
                    ->sortByDesc('date')
                    ->first()['status'] ?? null;
            }

            if (
                ! $hasNewerEvent
                && ! $hasNewerInterview
                && ! $hasNewerAssessment
                && $event->status_from === null
            ) {
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

            $hasNewerAssessment = $jobApplication->technicalAssessments()
                ->where(function ($query) use ($event): void {
                    $query->where('received_at', '>', $event->occurred_at)
                        ->orWhere(function ($query) use ($event): void {
                            $query->whereNull('received_at')
                                ->where('created_at', '>', $event->occurred_at);
                        })
                        ->orWhere(function ($query) use ($event): void {
                            $query->where('status', TechnicalAssessmentStatus::Finished->value)
                                ->whereNotNull('finished_at')
                                ->where('finished_at', '>', $event->occurred_at);
                        });
                })
                ->exists();

            if ($hasNewerEvent || $hasNewerInterview || $hasNewerAssessment) {
                return;
            }

            $attributes = [];

            if ($event->next_action_at !== null) {
                $attributes['next_action_at'] = $event->next_action_at;
            }

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

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
