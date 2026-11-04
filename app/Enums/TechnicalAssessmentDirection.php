<?php

namespace App\Enums;

enum TechnicalAssessmentDirection: string
{
    case Received = 'received';
    case Sent = 'sent';

    public function label(): string
    {
        return __('enums.technical_assessment_direction.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $technicalAssessmentDirection) => [
                $technicalAssessmentDirection->value => $technicalAssessmentDirection->label(),
            ])
            ->all();
    }
}
