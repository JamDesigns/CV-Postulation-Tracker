<?php

namespace App\Filament\Widgets;

use App\Models\JobApplication;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class JobSearchApplicationsEvolutionChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    public ?string $periodStart = null;

    public function getHeading(): string
    {
        return __('job-search-statistics.charts.applications_evolution');
    }

    protected function getData(): array
    {
        $dates = JobApplication::query()
            ->whereNotNull('sent_at', 'and')
            ->when(
                $this->periodStart,
                fn (Builder $query, string $periodStart): Builder => $query
                    ->whereDate('sent_at', '>=', $periodStart),
            )
            ->orderBy('sent_at')
            ->pluck('sent_at')
            ->map(fn ($date): Carbon => Carbon::parse($date));

        if ($dates->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => __('job-search-statistics.stats.sent'),
                        'data' => [],
                    ],
                ],
                'labels' => [],
            ];
        }

        $isCurrentMonth = $this->periodStart !== null
            && Carbon::parse($this->periodStart)
                ->isSameDay(now()->startOfMonth());

        if ($isCurrentMonth) {
            $days = collect();

            for (
                $day = Carbon::parse($this->periodStart)->startOfDay();
                $day->lte(now()->startOfDay());
                $day->addDay()
            ) {
                $days->put($day->format('Y-m-d'), 0);
            }

            foreach ($dates as $date) {
                $key = $date->format('Y-m-d');

                $days->put($key, $days->get($key, 0) + 1);
            }

            return [
                'datasets' => [
                    [
                        'label' => __(
                            'job-search-statistics.stats.sent',
                        ).' | '.Carbon::parse($this->periodStart)
                            ->locale(app()->getLocale())
                            ->translatedFormat('M Y'),
                        'data' => $days->values()->all(),
                    ],
                ],
                'labels' => $days
                    ->keys()
                    ->map(
                        fn (string $day): string => Carbon::parse($day)
                            ->translatedFormat('d'),
                    )
                    ->all(),
            ];
        }

        $start = $this->periodStart !== null
            ? Carbon::parse($this->periodStart)->startOfMonth()
            : $dates->first()->copy()->startOfMonth();

        $end = now()->startOfMonth();

        $months = collect();

        for ($month = $start->copy(); $month->lte($end); $month->addMonth()) {
            $months->put($month->format('Y-m'), 0);
        }

        foreach ($dates as $date) {
            $key = $date->format('Y-m');

            $months->put($key, $months->get($key, 0) + 1);
        }

        return [
            'datasets' => [
                [
                    'label' => __('job-search-statistics.stats.sent'),
                    'data' => $months->values()->all(),
                ],
            ],
            'labels' => $months
                ->keys()
                ->map(
                    fn (string $month): string => Carbon::createFromFormat('Y-m', $month)
                        ->locale(app()->getLocale())
                        ->translatedFormat('M Y'),
                )
                ->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'ticks' => [
                        'autoSkip' => true,
                        'maxTicksLimit' => 8,
                    ],
                ],
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
}
