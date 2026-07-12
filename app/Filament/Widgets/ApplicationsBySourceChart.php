<?php

namespace App\Filament\Widgets;

use App\Enums\SourceType;
use App\Models\JobApplication;
use Filament\Widgets\ChartWidget;

class ApplicationsBySourceChart extends ChartWidget
{
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 1;

    public function getHeading(): string
    {
        return __('dashboard.charts.applications_by_source.heading');
    }

    protected function getData(): array
    {
        $counts = JobApplication::query()
            ->selectRaw('source, count(*) as aggregate')
            ->groupBy('source')
            ->pluck('aggregate', 'source')
            ->all();

        $sources = SourceType::cases();

        $backgroundColors = array_map(
            fn(SourceType $source): string => match ($source) {
                SourceType::Linkedin => '#0a66c2',
                SourceType::Infojobs => '#167db7',
                SourceType::Indeed => '#2557a7',
                SourceType::Agency => '#8b5cf6',
                SourceType::Recruiter => '#14b8a6',
                SourceType::Email => '#f59e0b',
                SourceType::CompanyWebsite => '#10b981',
                SourceType::Other => '#64748b',
            },
            $sources,
        );

        return [
            'datasets' => [
                [
                    'data' => array_map(
                        fn(SourceType $source): int => $counts[$source->value] ?? 0,
                        $sources,
                    ),
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $backgroundColors,
                ],
            ],
            'labels' => array_map(
                fn(SourceType $source): string => $source->label(),
                $sources,
            ),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
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
        return 'bar';
    }
}
