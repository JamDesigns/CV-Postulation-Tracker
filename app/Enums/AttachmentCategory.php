<?php

namespace App\Enums;

enum AttachmentCategory: string
{
    case JobPosting = 'job_posting';
    case Application = 'application';
    case TechnicalTest = 'technical_test';
    case Interview = 'interview';
    case CompanyDocumentation = 'company_documentation';
    case EmploymentOffer = 'employment_offer';
    case Contract = 'contract';
    case Communication = 'communication';
    case Other = 'other';

    public function label(): string
    {
        return __('enums.attachment_category.'.$this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $category) => [$category->value => $category->label()])
            ->all();
    }
}
