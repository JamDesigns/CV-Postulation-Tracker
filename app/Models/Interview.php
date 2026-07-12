<?php

namespace App\Models;

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

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }
}
