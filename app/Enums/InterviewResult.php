<?php

namespace App\Enums;

enum InterviewResult: string
{
    case Pending = 'pending';
    case Passed = 'passed';
    case Rejected = 'rejected';
    case WaitingFeedback = 'waiting_feedback';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __('enums.interview_result.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Passed => 'success',
            self::Rejected,
            self::Cancelled => 'danger',
            default => 'info',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $interviewResult) => [$interviewResult->value => $interviewResult->label()])
            ->all();
    }
}
