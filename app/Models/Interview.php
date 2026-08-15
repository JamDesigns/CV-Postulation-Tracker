<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\InterviewResult;
use App\Enums\InterviewType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
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

            $status = ApplicationStatus::Interview;

            $jobApplication->forceFill([
                'status' => $status,
                'next_step' => $status->nextStep(),
                'next_action_at' => $interview->interview_at?->toDateString(),
            ])->save();
        });
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }
}
