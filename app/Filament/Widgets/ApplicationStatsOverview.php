<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicationStatus;
use App\Enums\InterviewResult;
use App\Models\CvVersion;
use App\Models\Interview;
use App\Models\JobApplication;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ApplicationStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $activeApplications = JobApplication::query()
            ->whereNotIn('status', [
                ApplicationStatus::Rejected->value,
                ApplicationStatus::Hired->value,
                ApplicationStatus::Paused->value,
            ], 'and')
            ->count('*');

        $sentWithoutResponse = JobApplication::query()
            ->whereIn('status', [
                ApplicationStatus::Sent->value,
                ApplicationStatus::FollowUpSent->value,
            ], 'and', false)
            ->count('*');

        $inProcess = JobApplication::query()
            ->whereIn('status', [
                ApplicationStatus::Interview->value,
                ApplicationStatus::TechnicalTest->value,
            ], 'and', false)
            ->count('*');

        $upcomingInterviews = Interview::query()
            ->where('interview_at', '>=', now(), 'and')
            ->whereIn('result', [
                InterviewResult::Pending->value,
                InterviewResult::WaitingFeedback->value,
            ], 'and', false)
            ->count('*');

        $adaptedCvs = CvVersion::query()->count('*');

        $rejectedApplications = JobApplication::query()
            ->where('status', '=', ApplicationStatus::Rejected->value, 'and')
            ->count('*');

        return [
            Stat::make(__('dashboard.stats.active_applications'), $activeApplications)
                ->description(__('dashboard.stats.active_applications_description'))
                ->descriptionIcon(Heroicon::Briefcase)
                ->color('primary'),

            Stat::make(__('dashboard.stats.sent_without_response'), $sentWithoutResponse)
                ->description(__('dashboard.stats.sent_without_response_description'))
                ->descriptionIcon(Heroicon::Clock)
                ->color('warning'),

            Stat::make(__('dashboard.stats.in_process'), $inProcess)
                ->description(__('dashboard.stats.in_process_description'))
                ->descriptionIcon(Heroicon::ChatBubbleLeftRight)
                ->color('info'),

            Stat::make(__('dashboard.stats.upcoming_interviews'), $upcomingInterviews)
                ->description(__('dashboard.stats.upcoming_interviews_description'))
                ->descriptionIcon(Heroicon::CalendarDays)
                ->color('success'),

            Stat::make(__('dashboard.stats.adapted_cvs'), $adaptedCvs)
                ->description(__('dashboard.stats.adapted_cvs_description'))
                ->descriptionIcon(Heroicon::DocumentText)
                ->color('gray'),

            Stat::make(__('dashboard.stats.rejected_applications'), $rejectedApplications)
                ->description(__('dashboard.stats.rejected_applications_description'))
                ->descriptionIcon(Heroicon::XCircle)
                ->color('danger'),
        ];
    }
}
