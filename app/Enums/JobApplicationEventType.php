<?php

namespace App\Enums;

enum JobApplicationEventType: string
{
    case ManualNote = 'manual_note';
    case StatusChanged = 'status_changed';
    case ApplicationSent = 'application_sent';
    case CommunicationSent = 'communication_sent';
    case CommunicationReceived = 'communication_received';
    case InquirySent = 'inquiry_sent';
    case ResponseReceived = 'response_received';
    case TechnicalTest = 'technical_test';
    case FollowUpSent = 'follow_up_sent';
    case Paused = 'paused';
    case Rejected = 'rejected';
    case Reopened = 'reopened';
    case Hired = 'hired';

    public function label(): string
    {
        return match ($this) {
            self::ManualNote => __('job-application-events.types.manual_note'),
            self::StatusChanged => __('job-application-events.types.status_changed'),
            self::ApplicationSent => __('job-application-events.types.application_sent'),
            self::CommunicationSent => __('job-application-events.types.communication_sent'),
            self::CommunicationReceived => __('job-application-events.types.communication_received'),
            self::InquirySent => __('job-application-events.types.inquiry_sent'),
            self::ResponseReceived => __('job-application-events.types.response_received'),
            self::TechnicalTest => __('job-application-events.types.technical_test'),
            self::FollowUpSent => __('job-application-events.types.follow_up_sent'),
            self::Paused => __('job-application-events.types.paused'),
            self::Rejected => __('job-application-events.types.rejected'),
            self::Reopened => __('job-application-events.types.reopened'),
            self::Hired => __('job-application-events.types.hired'),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->toArray();
    }
}
