<?php

namespace App\Support;

use App\Enums\Currency;
use NumberFormatter;

class CurrencyFormatter
{
    public static function format(
        int|float|string $amount,
        Currency $currency,
        ?string $locale = null,
    ): string {
        $formatter = new NumberFormatter(
            self::intlLocale($locale ?? app()->getLocale()),
            NumberFormatter::CURRENCY,
        );

        $numericAmount = (float) $amount;
        $hasDecimals = floor($numericAmount) !== $numericAmount;

        $formatter->setAttribute(
            NumberFormatter::MIN_FRACTION_DIGITS,
            $hasDecimals ? 2 : 0,
        );
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);

        return $formatter->formatCurrency(
            $numericAmount,
            $currency->value,
        );
    }

    private static function intlLocale(string $locale): string
    {
        return match ($locale) {
            'en' => 'en_GB',
            'fr' => 'fr_FR',
            default => 'es_ES',
        };
    }
}
