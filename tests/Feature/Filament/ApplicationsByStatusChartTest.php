<?php

use App\Enums\ApplicationStatus;
use App\Filament\Widgets\ApplicationsByStatusChart;
use App\Models\JobApplication;
use Illuminate\Support\Carbon;

afterEach(function (): void {
    Carbon::setTestNow();
});

function dashboardStatusChartData(): array
{
    $widget = new class extends ApplicationsByStatusChart
    {
        public function chartData(): array
        {
            return $this->getData();
        }
    };

    return $widget->chartData();
}

test('it groups applications by status and reference year for the last five years', function () {
    Carbon::setTestNow('2026-09-21 10:00:00');

    $companyId = companyId('Dashboard Status Chart Company');

    JobApplication::query()->create([
        'company_id' => $companyId,
        'job_title' => 'Application from 2022',
        'status' => ApplicationStatus::Sent,
        'sent_at' => '2022-03-10',
    ]);

    JobApplication::query()->create([
        'company_id' => $companyId,
        'job_title' => 'Application sent in 2023',
        'status' => ApplicationStatus::Rejected,
        'sent_at' => '2023-06-15',
    ]);

    $unsentApplication = JobApplication::query()->create([
        'company_id' => $companyId,
        'job_title' => 'Unsent application from 2024',
        'status' => ApplicationStatus::Pending,
        'sent_at' => null,
    ]);

    $unsentApplication->forceFill([
        'created_at' => '2024-04-20 10:00:00',
        'updated_at' => '2024-04-20 10:00:00',
    ])->saveQuietly();

    JobApplication::query()->create([
        'company_id' => $companyId,
        'job_title' => 'Application from 2026',
        'status' => ApplicationStatus::Interview,
        'sent_at' => '2026-08-01',
    ]);

    JobApplication::query()->create([
        'company_id' => $companyId,
        'job_title' => 'Application outside the window',
        'status' => ApplicationStatus::Sent,
        'sent_at' => '2021-12-31',
    ]);

    $data = dashboardStatusChartData();

    $datasets = collect($data['datasets'])
        ->keyBy(fn (array $dataset): int => (int) substr($dataset['label'], 0, 4));

    $statusIndexes = collect(ApplicationStatus::cases())
        ->mapWithKeys(
            fn (ApplicationStatus $status, int $index): array => [
                $status->value => $index,
            ],
        );

    expect($datasets->keys()->all())
        ->toBe([2022, 2023, 2024, 2025, 2026])
        ->and($datasets[2022]['data'][$statusIndexes[ApplicationStatus::Sent->value]])
        ->toBe(1)
        ->and($datasets[2023]['data'][$statusIndexes[ApplicationStatus::Rejected->value]])
        ->toBe(1)
        ->and($datasets[2024]['data'][$statusIndexes[ApplicationStatus::Pending->value]])
        ->toBe(1)
        ->and(array_sum($datasets[2025]['data']))
        ->toBe(0)
        ->and($datasets[2026]['data'][$statusIndexes[ApplicationStatus::Interview->value]])
        ->toBe(1)
        ->and($datasets[2026]['data'][$statusIndexes[ApplicationStatus::Sent->value]])
        ->toBe(0)
        ->and(array_sum(
            $datasets
                ->flatMap(fn (array $dataset): array => $dataset['data'])
                ->all(),
        ))
        ->toBe(4);
});

test('it automatically rolls the five year window forward', function () {
    Carbon::setTestNow('2027-01-01 10:00:00');

    $data = dashboardStatusChartData();

    $years = collect($data['datasets'])
        ->map(
            fn (array $dataset): int => (int) substr($dataset['label'], 0, 4),
        )
        ->all();

    expect($years)->toBe([
        2023,
        2024,
        2025,
        2026,
        2027,
    ]);
});
