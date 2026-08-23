<?php

namespace App\Enums;

enum SourceType: string
{
    case Linkedin = 'linkedin';
    case Infojobs = 'infojobs';
    case Indeed = 'indeed';
    case Agency = 'agency';
    case Recruiter = 'recruiter';
    case Email = 'email';
    case CompanyWebsite = 'company_website';
    case Other = 'other';

    public function label(): string
    {
        return __('enums.source_type.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $sourceType) => [$sourceType->value => $sourceType->label()])
            ->all();
    }
}
