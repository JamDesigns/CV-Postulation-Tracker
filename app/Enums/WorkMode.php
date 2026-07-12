<?php

namespace App\Enums;

enum WorkMode: string
{
    case Remote = 'remote';
    case Hybrid = 'hybrid';
    case Onsite = 'onsite';
    case NotSpecified = 'not_specified';

    public function label(): string
    {
        return __('enums.work_mode.' . $this->value);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $workMode) => [$workMode->value => $workMode->label()])
            ->all();
    }
}
