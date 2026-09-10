<?php

namespace App\Enums;

enum ApplicationRejectionReason: string
{
    case Salary = 'salary';
    case WorkMode = 'work_mode';
    case Language = 'language';
    case Experience = 'experience';
    case TechnicalFit = 'technical_fit';
    case PositionClosed = 'position_closed';
    case CompanyRejection = 'company_rejection';
    case NoResponse = 'no_response';
    case Other = 'other';

    public function label(): string
    {
        return __('enums.application_rejection_reason.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(
                fn (self $reason) => [$reason->value => $reason->label()],
            )
            ->all();
    }
}
