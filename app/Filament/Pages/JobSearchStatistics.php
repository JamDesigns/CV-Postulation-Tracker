<?php

namespace App\Filament\Pages;

use App\Enums\ApplicationStatus;
use App\Enums\JobApplicationEventType;
use App\Models\JobApplication;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class JobSearchStatistics extends Page
{
    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected string $view = 'filament.pages.job-search-statistics';

    public function getTitle(): string
    {
        return __('navigation.items.job_search_statistics');
    }

    public static function getNavigationLabel(): string
    {
        return __('navigation.items.job_search_statistics');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.applications');
    }

    public string $period = 'all';

    public function periodOptions(): array
    {
        return [
            'all' => __('job-search-statistics.period.all'),
            'year' => __('job-search-statistics.period.year'),
            'last_6_months' => __('job-search-statistics.period.last_6_months'),
            'last_3_months' => __('job-search-statistics.period.last_3_months'),
            'month' => __('job-search-statistics.period.month'),
        ];
    }

    public function periodStart(): ?Carbon
    {
        return match ($this->period) {
            'year' => now()->startOfYear(),
            'last_6_months' => now()->subMonths(5)->startOfMonth(),
            'last_3_months' => now()->subMonths(2)->startOfMonth(),
            'month' => now()->startOfMonth(),
            default => null,
        };
    }

    protected function applicationsQuery(): Builder
    {
        return JobApplication::query()
            ->whereNotNull('sent_at', 'and')
            ->when(
                $this->periodStart(),
                fn (Builder $query, Carbon $start): Builder => $query
                    ->whereDate('sent_at', '>=', $start->toDateString()),
            );
    }

    public function statistics(): array
    {
        $applications = $this->applicationsQuery();

        return [
            'sent' => (clone $applications)->count(),

            'responses' => (clone $applications)
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
                ->count(),

            'interviews' => (clone $applications)
                ->whereHas('interviews')
                ->count(),

            'technical_tests' => (clone $applications)
                ->where(function (Builder $query): void {
                    $query
                        ->where('status', '=', ApplicationStatus::TechnicalTest->value, 'and')
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
                ->count(),

            'rejected' => (clone $applications)
                ->where('status', '=', ApplicationStatus::Rejected->value, 'and')
                ->count(),

            'hired' => (clone $applications)
                ->where('status', '=', ApplicationStatus::Hired->value, 'and')
                ->count(),
        ];
    }

    public function percentage(int $value, int $total): float
    {
        if ($total === 0) {
            return 0.0;
        }

        return round(($value / $total) * 100, 1);
    }
}
