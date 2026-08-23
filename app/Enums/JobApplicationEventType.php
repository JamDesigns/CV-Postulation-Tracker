<?php

namespace App\Enums;

enum JobApplicationEventType: string
{
    case StatusChanged = 'status_changed';
    case NoteAdded = 'note_added';
    case ApplicationSent = 'application_sent';
    case ResponseReceived = 'response_received';
    case TechnicalTest = 'technical_test';
    case FollowUpSent = 'follow_up_sent';
    case Paused = 'paused';
    case Rejected = 'rejected';
    case Reopened = 'reopened';
    case Hired = 'hired';
    case ManualNote = 'manual_note';

    public function label(): string
    {
        return match ($this) {
            self::StatusChanged => __('job-application-events.types.status_changed'),
            self::NoteAdded => __('job-application-events.types.note_added'),
            self::ApplicationSent => __('job-application-events.types.application_sent'),
            self::ResponseReceived => __('job-application-events.types.response_received'),
            self::TechnicalTest => __('job-application-events.types.technical_test'),
            self::FollowUpSent => __('job-application-events.types.follow_up_sent'),
            self::Paused => __('job-application-events.types.paused'),
            self::Rejected => __('job-application-events.types.rejected'),
            self::Reopened => __('job-application-events.types.reopened'),
            self::Hired => __('job-application-events.types.hired'),
            self::ManualNote => __('job-application-events.types.manual_note'),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->toArray();
    }
}
