<?php

namespace App\Enums;

enum ApplicationContactRole: string
{
    case Recruiter = 'recruiter';
    case HiringManager = 'hiring_manager';
    case Hr = 'hr';
    case TechnicalInterviewer = 'technical_interviewer';
    case Other = 'other';

    public function label(): string
    {
        return __('enums.application_contact_role.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $role) => [$role->value => $role->label()])
            ->all();
    }
}
