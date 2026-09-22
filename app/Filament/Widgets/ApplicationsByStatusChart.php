<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicationStatus;
use App\Models\JobApplication;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class ApplicationsByStatusChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return __('dashboard.charts.applications_by_status.heading');
    }

    protected function getData(): array
    {
        $currentYear = now()->year;
        $years = range($currentYear - 4, $currentYear);
        $statuses = ApplicationStatus::cases();

        $periodStart = now()->copy()->subYears(4)->startOfYear();
        $periodEnd = now();

        $counts = [];

        foreach ($years as $year) {
            foreach ($statuses as $status) {
                $counts[$year][$status->value] = 0;
            }
        }

        $applications = JobApplication::query()
            ->select([
                'status',
                'sent_at',
                'created_at',
            ])
            ->where(function (Builder $query) use ($periodStart, $periodEnd): void {
                $query
                    ->whereBetween('sent_at', [$periodStart, $periodEnd])
                    ->orWhere(function (Builder $query) use ($periodStart, $periodEnd): void {
                        $query
                            ->whereNull('sent_at')
                            ->whereBetween('created_at', [$periodStart, $periodEnd]);
                    });
            })
            ->get();

        foreach ($applications as $application) {
            $date = $application->sent_at ?? $application->created_at;
            $year = $date->year;
            $status = $application->status;

            if (! isset($counts[$year][$status->value])) {
                continue;
            }

            $counts[$year][$status->value]++;
        }

        $lineColors = [
            '#64748b',
            '#3b82f6',
            '#8b5cf6',
            '#f59e0b',
            '#10b981',
        ];

        $datasets = [];

        foreach ($years as $index => $year) {
            $datasets[] = [
                'label' => $year === $currentYear
                    ? __('dashboard.charts.applications_by_status.current_year', [
                        'year' => $year,
                    ])
                    : (string) $year,
                'data' => array_map(
                    fn (ApplicationStatus $status): int => $counts[$year][$status->value],
                    $statuses,
                ),
                'borderColor' => $lineColors[$index],
                'backgroundColor' => $lineColors[$index],
                'fill' => false,
                'tension' => 0.25,
                'pointRadius' => 3,
                'pointHoverRadius' => 5,
                'borderWidth' => 2,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => array_map(
                fn (ApplicationStatus $status): string => $status->label(),
                $statuses,
            ),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getMaxHeight(): ?string
    {
        return '340px';
    }
}
