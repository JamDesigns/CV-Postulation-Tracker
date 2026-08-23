<?php

namespace App\Enums;

enum InterviewType: string
{
    case Hr = 'hr';
    case Technical = 'technical';
    case Client = 'client';
    case CultureFit = 'culture_fit';
    case Final = 'final';
    case Other = 'other';

    public function label(): string
    {
        return __('enums.interview_type.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $interviewType) => [$interviewType->value => $interviewType->label()])
            ->all();
    }
}
