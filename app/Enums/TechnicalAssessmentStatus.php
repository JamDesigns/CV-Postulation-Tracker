<?php

namespace App\Enums;

enum TechnicalAssessmentStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Finished = 'finished';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __('enums.technical_assessment_status.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::InProgress => 'info',
            self::Finished => 'success',
            self::Cancelled => 'gray',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $technicalAssessmentStatus) => [
                $technicalAssessmentStatus->value => $technicalAssessmentStatus->label(),
            ])
            ->all();
    }
}
