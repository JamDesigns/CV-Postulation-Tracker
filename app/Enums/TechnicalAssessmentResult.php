<?php

namespace App\Enums;

enum TechnicalAssessmentResult: string
{
    case Pending = 'pending';
    case Passed = 'passed';
    case NotPassed = 'not_passed';

    public function label(): string
    {
        return __('enums.technical_assessment_result.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Passed => 'success',
            self::NotPassed => 'danger',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $technicalAssessmentResult) => [
                $technicalAssessmentResult->value => $technicalAssessmentResult->label(),
            ])
            ->all();
    }
}
