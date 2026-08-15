<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Responded = 'responded';
    case Interview = 'interview';
    case TechnicalTest = 'technical_test';
    case FollowUpSent = 'follow_up_sent';
    case Rejected = 'rejected';
    case Paused = 'paused';
    case Hired = 'hired';

    public function label(): string
    {
        return __('enums.application_status.'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Sent => 'success',
            self::Rejected => 'danger',
            default => 'info',
        };
    }

    public function nextStep(): ?string
    {
        return match ($this) {
            self::Pending => __('job-applications.quick_actions.next_steps.send_application'),
            self::Sent => __('job-applications.quick_actions.next_steps.send_follow_up'),
            self::Responded => __('job-applications.quick_actions.next_steps.review_response'),
            self::Interview => __('job-applications.quick_actions.next_steps.prepare_interview'),
            self::TechnicalTest => __('job-applications.quick_actions.next_steps.complete_technical_test'),
            self::FollowUpSent => __('job-applications.quick_actions.next_steps.wait_after_follow_up'),
            self::Paused => __('job-applications.quick_actions.next_steps.review_paused_application'),
            self::Rejected, self::Hired => null,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }
}
