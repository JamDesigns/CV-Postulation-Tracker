<?php

namespace App\Filament\Widgets;

use App\Enums\InterviewResult;
use App\Enums\InterviewType;
use App\Filament\Resources\JobApplications\JobApplicationResource;
use App\Models\Interview;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingInterviewsTable extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('dashboard.widgets.upcoming_interviews.heading'))
            ->emptyStateHeading(__('dashboard.widgets.upcoming_interviews.empty'))
            ->query(
                Interview::query()
                    ->with('jobApplication.company')
                    ->where('interview_at', '>=', now())
                    ->where('result', '=', InterviewResult::Pending->value, 'and')
                    ->orderBy('interview_at')
            )
            ->recordUrl(
                fn (Interview $record): ?string => $record->jobApplication
                    ? JobApplicationResource::getUrl(
                        'view',
                        ['record' => $record->jobApplication],
                    )
                    : null,
            )
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('jobApplication.job_title')
                    ->label(__('dashboard.widgets.upcoming_interviews.application'))
                    ->searchable()
                    ->limit(35),

                TextColumn::make('jobApplication.company.name')
                    ->label(__('dashboard.widgets.upcoming_interviews.company'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('interview_at')
                    ->label(__('dashboard.widgets.upcoming_interviews.date'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('interview_type')
                    ->label(__('dashboard.widgets.upcoming_interviews.type'))
                    ->formatStateUsing(fn (InterviewType|string|null $state): ?string => $state instanceof InterviewType
                            ? $state->label()
                            : InterviewType::tryFrom((string) $state)?->label() ?? $state)
                    ->badge(),

                TextColumn::make('result')
                    ->label(__('dashboard.widgets.upcoming_interviews.result'))
                    ->formatStateUsing(fn (InterviewResult|string|null $state): ?string => $state instanceof InterviewResult
                            ? $state->label()
                            : InterviewResult::tryFrom((string) $state)?->label() ?? $state)
                    ->badge()
                    ->color(fn (InterviewResult|string|null $state): string => $state instanceof InterviewResult
                            ? $state->color()
                            : InterviewResult::tryFrom((string) $state)?->color() ?? 'info'),

                TextColumn::make('people')
                    ->label(__('dashboard.widgets.upcoming_interviews.people'))
                    ->limit(40)
                    ->wrap()
                    ->toggleable(),
            ]);
    }
}
