<?php

namespace App\Enums;

enum Currency: string
{
    case EUR = 'EUR';
    case USD = 'USD';
    case GBP = 'GBP';

    public function label(): string
    {
        return $this->symbol();
    }

    public function symbol(): string
    {
        return match ($this) {
            self::EUR => '€',
            self::USD => '$',
            self::GBP => '£',
        };
    }

    public static function localForLocale(?string $locale = null): self
    {
        return match ($locale ?? app()->getLocale()) {
            'en' => self::GBP,
            default => self::EUR,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $currency) => [$currency->value => $currency->label()])
            ->all();
    }
}
