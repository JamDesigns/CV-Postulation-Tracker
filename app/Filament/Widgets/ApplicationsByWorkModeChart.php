<?php

namespace App\Filament\Widgets;

use App\Enums\WorkMode;
use App\Models\JobApplication;
use Filament\Widgets\ChartWidget;

class ApplicationsByWorkModeChart extends ChartWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 1;

    public function getHeading(): string
    {
        return __('dashboard.charts.applications_by_work_mode.heading');
    }

    protected function getData(): array
    {
        $counts = JobApplication::query()
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
