<?php

namespace App\Enums;

enum ApplicationContactSource: string
{
    case Linkedin = 'linkedin';
    case Email = 'email';
    case JobOffer = 'job_offer';
    case CompanyWebsite = 'company_website';
    case Referral = 'referral';
    case Other = 'other';

    public function label(): string
    {
        return __('enums.application_contact_source.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $source) => [$source->value => $source->label()])
            ->all();
    }
}
