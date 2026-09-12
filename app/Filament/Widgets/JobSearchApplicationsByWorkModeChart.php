<?php

namespace App\Filament\Widgets;

use App\Enums\WorkMode;
use App\Models\JobApplication;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class JobSearchApplicationsByWorkModeChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    public ?string $periodStart = null;

    public function getHeading(): string
    {
        return __('job-search-statistics.charts.applications_by_work_mode');
    }

    protected function getData(): array
    {
        $counts = JobApplication::query()
            ->whereNotNull('sent_at', 'and')
            ->when(
                $this->periodStart,
                fn (Builder $query, string $periodStart): Builder => $query
                    ->whereDate('sent_at', '>=', $periodStart),
            )
            ->selectRaw('work_mode, count(*) as aggregate')
            ->groupBy('work_mode')
            ->pluck('aggregate', 'work_mode')
            ->all();

        $workModes = WorkMode::cases();

        $backgroundColors = array_map(
            fn (WorkMode $workMode): string => match ($workMode) {
                WorkMode::Remote => '#10b981',
                WorkMode::Hybrid => '#f59e0b',
                WorkMode::Onsite => '#f43f5e',
                WorkMode::NotSpecified => '#64748b',
            },
            $workModes,
        );

        return [
            'datasets' => [
                [
                    'data' => array_map(
                        fn (WorkMode $workMode): int => $counts[$workMode->value] ?? 0,
                        $workModes,
                    ),
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $backgroundColors,
                ],
            ],
            'labels' => array_map(
                fn (WorkMode $workMode): string => $workMode->label(),
                $workModes,
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
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
