<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicationRejectionReason;
use App\Enums\ApplicationStatus;
use App\Models\JobApplication;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class JobSearchRejectionReasonsChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    public ?string $periodStart = null;

    public function getHeading(): string
    {
        return __('job-search-statistics.charts.rejection_reasons');
    }

    protected function getData(): array
    {
        $applications = JobApplication::query()
            ->whereNotNull('sent_at', 'and')
            ->when(
                $this->periodStart,
                fn (Builder $query, string $periodStart): Builder => $query
                    ->whereDate('sent_at', '>=', $periodStart),
            )
            ->where('status', '=', ApplicationStatus::Rejected->value, 'and');

        $counts = (clone $applications)
            ->whereNotNull('rejection_reason', 'and')
            ->selectRaw('rejection_reason, count(*) as aggregate')
            ->groupBy('rejection_reason')
            ->pluck('aggregate', 'rejection_reason')
            ->all();

        $unspecifiedCount = (clone $applications)
            ->whereNull('rejection_reason', 'and')
            ->count();

        $reasons = collect(ApplicationRejectionReason::cases())
            ->filter(
                fn (ApplicationRejectionReason $reason): bool => ($counts[$reason->value] ?? 0) > 0,
            );

        $labels = $reasons
            ->map(fn (ApplicationRejectionReason $reason): string => $reason->label())
            ->values()
            ->all();

        $data = $reasons
            ->map(fn (ApplicationRejectionReason $reason): int => $counts[$reason->value] ?? 0)
            ->values()
            ->all();

        if ($unspecifiedCount > 0) {
            $labels[] = __('job-search-statistics.charts.rejection_reason_unspecified');
            $data[] = $unspecifiedCount;
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
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
