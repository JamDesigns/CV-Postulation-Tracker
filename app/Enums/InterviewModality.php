<?php

namespace App\Enums;

enum InterviewModality: string
{
    case Phone = 'phone';
    case VideoCall = 'video_call';
    case InPerson = 'in_person';

    public function label(): string
    {
        return __('enums.interview_modality.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $interviewModality) => [
                $interviewModality->value => $interviewModality->label(),
            ])
            ->all();
    }
}
