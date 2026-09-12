<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicationStatus;
use App\Enums\JobApplicationEventType;
use App\Models\JobApplication;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class JobSearchFunnelChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    public ?string $periodStart = null;

    public function getHeading(): string
    {
        return __('job-search-statistics.charts.funnel');
    }

    protected function getData(): array
    {
        $applications = JobApplication::query()
            ->whereNotNull('sent_at', 'and')
            ->when(
                $this->periodStart,
                fn (Builder $query, string $periodStart): Builder => $query
                    ->whereDate('sent_at', '>=', $periodStart),
            );

        $sent = (clone $applications)->count();

        $responded = (clone $applications)
            ->where(function (Builder $query): void {
                $query
                    ->where('status', '=', ApplicationStatus::Responded->value, 'and')
                    ->orWhereHas(
                        'events',
                        fn (Builder $query): Builder => $query->where(
                            'type',
                            '=',
                            JobApplicationEventType::ResponseReceived->value,
                            'and',
                        ),
                    );
            })
            ->count();

        $interviewOrTest = (clone $applications)
            ->where(function (Builder $query): void {
                $query
                    ->whereHas('interviews')
                    ->orWhere('status', '=', ApplicationStatus::TechnicalTest->value)
                    ->orWhereHas(
                        'events',
                        fn (Builder $query): Builder => $query->where(
                            'type',
                            '=',
                            JobApplicationEventType::TechnicalTest->value,
                            'and',
                        ),
                    );
            })
            ->count();

        $hired = (clone $applications)
            ->where('status', '=', ApplicationStatus::Hired->value, 'and')
            ->count();

        return [
            'datasets' => [
                [
                    'data' => [
                        $sent,
                        $responded,
                        $interviewOrTest,
                        $hired,
                    ],
                ],
            ],
            'labels' => [
                __('job-search-statistics.charts.funnel_stages.sent'),
                __('job-search-statistics.charts.funnel_stages.responded'),
                __('job-search-statistics.charts.funnel_stages.interview_or_test'),
                __('job-search-statistics.charts.funnel_stages.hired'),
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
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
        return 'bar';
    }
}
