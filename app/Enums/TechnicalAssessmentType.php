<?php

namespace App\Enums;

enum TechnicalAssessmentType: string
{
    case AtHome = 'at_home';
    case Live = 'live';

    public function label(): string
    {
        return __('enums.technical_assessment_type.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $technicalAssessmentType) => [
                $technicalAssessmentType->value => $technicalAssessmentType->label(),
            ])
            ->all();
    }
}
