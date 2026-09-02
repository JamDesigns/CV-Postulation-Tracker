<?php

namespace App\Services;

use App\Enums\Currency;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use SimpleXMLElement;
use Throwable;

class ExchangeRateService
{
    private const ECB_DAILY_RATES_URL = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    public function replaceSnapshot(JobApplication $jobApplication): void
    {
        $jobApplication->exchangeRates()->delete();

        if ($jobApplication->salary_min === null || $jobApplication->currency === null) {
            return;
        }

        $sourceCurrency = $jobApplication->currency instanceof Currency
            ? $jobApplication->currency
            : Currency::tryFrom((string) $jobApplication->currency);

        if ($sourceCurrency === null) {
            return;
        }

        $snapshot = $this->snapshot($sourceCurrency);

        if ($snapshot === null) {
            return;
        }

        $jobApplication->exchangeRates()->createMany(
            collect($snapshot['rates'])
                ->map(fn (float $rate, string $currency): array => [
                    'currency' => $currency,
                    'rate' => $rate,
                    'rate_date' => $snapshot['date'],
                ])
                ->values()
                ->all(),
        );
    }

    public function rateFor(
        ?JobApplication $jobApplication,
        Currency $sourceCurrency,
        Currency $targetCurrency,
    ): ?float {
        if ($sourceCurrency === $targetCurrency) {
            return 1.0;
        }

        $storedCurrency = $jobApplication?->currency instanceof Currency
            ? $jobApplication->currency
            : Currency::tryFrom((string) $jobApplication?->currency);

        if ($jobApplication !== null && $storedCurrency === $sourceCurrency) {
            $exchangeRate = $jobApplication->exchangeRates()
                ->where('currency', $targetCurrency->value)
                ->first();

            return $exchangeRate === null
                ? null
                : (float) $exchangeRate->rate;
        }

        $snapshot = $this->snapshot($sourceCurrency);

        return $snapshot['rates'][$targetCurrency->value] ?? null;
    }

    public function snapshot(Currency $sourceCurrency): ?array
    {
        try {
            $data = $this->latestRates();
        } catch (Throwable) {
            return null;
        }

        $sourceRate = $data['rates'][$sourceCurrency->value] ?? null;

        if ($sourceRate === null) {
            return null;
        }

        $rates = [];

        foreach (Currency::cases() as $targetCurrency) {
            $targetRate = $data['rates'][$targetCurrency->value] ?? null;

            if ($targetRate === null) {
                continue;
            }

            $rates[$targetCurrency->value] = round(
                $targetRate / $sourceRate,
                10,
            );
        }

        return [
            'date' => $data['date'],
            'rates' => $rates,
        ];
    }

    private function latestRates(): array
    {
        return Cache::remember(
            'ecb.exchange_rates.latest',
            now()->addDay(),
            function (): array {
                $response = Http::timeout(10)
                    ->retry(2, 200)
                    ->get(self::ECB_DAILY_RATES_URL)
                    ->throw();

                $xml = simplexml_load_string($response->body());

                if (! $xml instanceof SimpleXMLElement) {
                    throw new RuntimeException('Unable to parse ECB exchange rates.');
                }

                $dateNodes = $xml->xpath('//*[local-name()="Cube"][@time]');
                $rateNodes = $xml->xpath('//*[local-name()="Cube"][@currency][@rate]');

                if (! $dateNodes || ! $rateNodes) {
                    throw new RuntimeException('ECB exchange rates are unavailable.');
                }

                $rates = [
                    Currency::EUR->value => 1.0,
                ];

                foreach ($rateNodes as $rateNode) {
                    $currency = (string) $rateNode['currency'];

                    if (Currency::tryFrom($currency) === null) {
                        continue;
                    }

                    $rates[$currency] = (float) $rateNode['rate'];
                }

                return [
                    'date' => (string) $dateNodes[0]['time'],
                    'rates' => $rates,
                ];
            },
        );
    }
}
