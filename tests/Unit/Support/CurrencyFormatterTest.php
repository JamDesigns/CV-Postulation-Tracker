<?php

use App\Enums\Currency;
use App\Support\CurrencyFormatter;

test('it returns the local currency for each supported locale', function () {
    expect(Currency::localForLocale('es'))->toBe(Currency::EUR)
        ->and(Currency::localForLocale('en'))->toBe(Currency::GBP)
        ->and(Currency::localForLocale('fr'))->toBe(Currency::EUR);
});

test('it formats euros for the spanish locale', function () {
    $formatted = CurrencyFormatter::format(42000, Currency::EUR, 'es');

    $normalized = str_replace(
        ["\u{00A0}", "\u{202F}"],
        ' ',
        $formatted,
    );

    expect($normalized)->toBe('42.000 €');
});

test('it formats pounds for the english locale', function () {
    expect(CurrencyFormatter::format(31130.40, Currency::GBP, 'en'))
        ->toBe('£31,130.40');
});

test('it formats euros for the french locale', function () {
    $formatted = CurrencyFormatter::format(42000, Currency::EUR, 'fr');

    $normalized = str_replace(
        ["\u{00A0}", "\u{202F}"],
        ' ',
        $formatted,
    );

    expect($normalized)->toBe('42 000 €');
});
