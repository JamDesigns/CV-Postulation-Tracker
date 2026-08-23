<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\InterviewResult;
use App\Enums\InterviewType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Interview extends Model
{
    use HasTranslations;

    public array $translatable = [
        'people',
        'expected_questions',
        'strengths_to_defend',
        'risks_to_clarify',
        'notes',
    ];

    protected $fillable = [
        'job_application_id',
        'interview_at',
        'interview_type',
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

            $hasNewerEvent = $jobApplication->events()
                ->where('occurred_at', '>', $interview->interview_at)
                ->exists();

            $hasNewerInterview = $jobApplication->interviews()
                ->where('id', '!=', $interview->getKey())
                ->where('interview_at', '>', $interview->interview_at)
                ->exists();

            if ($hasNewerEvent || $hasNewerInterview) {
                return;
            }

            if ($interview->result === InterviewResult::Rejected) {
                $jobApplication->forceFill([
                    'status' => ApplicationStatus::Rejected,
                    'next_step' => null,
                    'next_action_at' => null,
                ])->save();

                return;
            }

            if ($interview->result === InterviewResult::Cancelled) {
                return;
            }

            $status = ApplicationStatus::Interview;
            $locale = $interview->getLocale();

            $jobApplication->setLocale($locale);

            $jobApplication->forceFill([
                'status' => $status,
                'next_step' => $status->nextStep($locale),
                'next_action_at' => $interview->interview_at?->toDateString(),
            ])->save();
        });
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }
}
