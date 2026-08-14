<?php

use App\Enums\Currency;
use App\Models\JobApplication;
use App\Services\ExchangeRateService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

$ecbXml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<gesmes:Envelope
    xmlns:gesmes="http://www.gesmes.org/xml/2002-08-01"
    xmlns="http://www.ecb.int/vocabulary/2002-08-01/eurofxref"
>
    <Cube>
        <Cube time="2026-08-13">
            <Cube currency="USD" rate="1.2000"/>
            <Cube currency="GBP" rate="0.8000"/>
        </Cube>
    </Cube>
</gesmes:Envelope>
XML;

beforeEach(function () {
    Cache::forget('ecb.exchange_rates.latest');
});

test('it normalizes the ECB rates against the source currency', function () use ($ecbXml) {
    Http::fake([
        'https://www.ecb.europa.eu/*' => Http::response($ecbXml),
    ]);

    $snapshot = app(ExchangeRateService::class)->snapshot(Currency::USD);

    expect($snapshot)
        ->not->toBeNull()
        ->and($snapshot['date'])->toBe('2026-08-13')
        ->and($snapshot['rates']['EUR'])->toBe(0.8333333333)
        ->and($snapshot['rates']['USD'])->toBe(1.0)
        ->and($snapshot['rates']['GBP'])->toBe(0.6666666667);
});

test('it caches the latest ECB rates', function () use ($ecbXml) {
    Http::fake([
        'https://www.ecb.europa.eu/*' => Http::response($ecbXml),
    ]);

    $service = app(ExchangeRateService::class);

    $service->snapshot(Currency::USD);
    $service->snapshot(Currency::GBP);

    Http::assertSentCount(1);
});

test('it returns null when the ECB request fails', function () {
    Http::fake([
        'https://www.ecb.europa.eu/*' => Http::response(status: 500),
    ]);

    expect(app(ExchangeRateService::class)->snapshot(Currency::USD))
        ->toBeNull();
});

test('it uses the stored historical rate for an existing application', function () {
    $jobApplication = JobApplication::withoutEvents(
        fn (): JobApplication => JobApplication::query()->create([
            'company_name' => 'Test Company',
            'job_title' => 'Full Stack Developer',
            'salary' => 42000,
            'currency' => Currency::USD,
        ]),
    );

    $jobApplication->exchangeRates()->create([
        'currency' => Currency::GBP,
        'rate' => 0.75,
        'rate_date' => '2026-08-13',
    ]);

    Http::preventStrayRequests();

    $rate = app(ExchangeRateService::class)->rateFor(
        $jobApplication,
        Currency::USD,
        Currency::GBP,
    );

    expect($rate)->toBe(0.75);

    Http::assertNothingSent();
});
