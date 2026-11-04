<?php

namespace App\Enums;

enum TechnicalAssessmentModality: string
{
    case Phone = 'phone';
    case VideoCall = 'video_call';
    case InPerson = 'in_person';

    public function label(): string
    {
        return __('enums.technical_assessment_modality.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $technicalAssessmentModality) => [
                $technicalAssessmentModality->value => $technicalAssessmentModality->label(),
            ])
            ->all();
    }
}
