<?php
namespace App\Enums;

enum NextActionUrgency: string {
    case Overdue  = 'overdue';
    case Today    = 'today';
    case Upcoming = 'upcoming';
    case NoDate   = 'no_date';

    public function label(): string
    {
        return match ($this) {
            self::Overdue  => __('next-action-urgencies.overdue'),
            self::Today    => __('next-action-urgencies.today'),
            self::Upcoming => __('next-action-urgencies.upcoming'),
            self::NoDate   => __('next-action-urgencies.no_date'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Overdue  => 'danger',
            self::Today    => 'primary',
            self::Upcoming => 'info',
            self::NoDate   => 'gray',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn(self $urgency): array=> [$urgency->value => $urgency->label()])
            ->toArray();
    }
}
