<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicationStatus;
use App\Models\JobApplication;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class JobSearchApplicationsByStatusChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    public ?string $periodStart = null;

    public function getHeading(): string
    {
        return __('job-search-statistics.charts.applications_by_status');
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
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $statuses = ApplicationStatus::cases();

        $backgroundColors = array_map(
            fn (ApplicationStatus $status): string => match ($status) {
                ApplicationStatus::Pending => '#d1d5db',
                ApplicationStatus::Sent => '#3b82f6',
                ApplicationStatus::Responded => '#14b8a6',
                ApplicationStatus::Interview => '#8b5cf6',
                ApplicationStatus::TechnicalTest => '#6366f1',
                ApplicationStatus::FollowUpSent => '#f59e0b',
                ApplicationStatus::Rejected => '#f43f5e',
                ApplicationStatus::Paused => '#475569',
                ApplicationStatus::Hired => '#10b981',
            },
            $statuses,
        );

        return [
            'datasets' => [
                [
                    'data' => array_map(
                        fn (ApplicationStatus $status): int => $counts[$status->value] ?? 0,
                        $statuses,
                    ),
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $backgroundColors,
                ],
            ],
            'labels' => array_map(
                fn (ApplicationStatus $status): string => $status->label(),
                $statuses,
            ),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
