<?php
namespace App\Enums;

enum ApplicationStatus: string {
    case Pending       = 'pending';
    case Sent          = 'sent';
    case Responded     = 'responded';
    case Interview     = 'interview';
    case TechnicalTest = 'technical_test';
    case FollowUpSent  = 'follow_up_sent';
    case Rejected      = 'rejected';
    case Paused        = 'paused';
    case Hired         = 'hired';

    public function label(): string
    {
        return __('enums.application_status.' . $this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Sent     => 'success',
            self::Rejected => 'danger',
            default        => 'info',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $status) => [$status->value => $status->label()])
            ->all();
    }
}
