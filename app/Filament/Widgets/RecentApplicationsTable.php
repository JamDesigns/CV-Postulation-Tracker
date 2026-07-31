<?php

namespace App\Filament\Widgets;

use App\Enums\ApplicationStatus;
use App\Enums\SourceType;
use App\Enums\WorkMode;
use App\Models\JobApplication;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentApplicationsTable extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('dashboard.widgets.recent_applications.heading'))
            ->emptyStateHeading(__('dashboard.widgets.recent_applications.empty'))
            ->query(
                JobApplication::query()
                    ->latest('created_at')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('company_name')
                    ->label(__('dashboard.widgets.recent_applications.company'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('job_title')
                    ->label(__('dashboard.widgets.recent_applications.position'))
                    ->searchable()
                    ->limit(35),

                TextColumn::make('status')
                    ->label(__('dashboard.widgets.recent_applications.status'))
                    ->formatStateUsing(fn (ApplicationStatus|string|null $state): ?string => $state instanceof ApplicationStatus
                            ? $state->label()
                            : ApplicationStatus::tryFrom((string) $state)?->label() ?? $state)
                    ->badge()
                    ->color(fn (ApplicationStatus|string|null $state): string => $state instanceof ApplicationStatus
                            ? $state->color()
                            : ApplicationStatus::tryFrom((string) $state)?->color() ?? 'info'),

                TextColumn::make('source')
                    ->label(__('dashboard.widgets.recent_applications.source'))
                    ->formatStateUsing(fn (SourceType|string|null $state): ?string => $state instanceof SourceType
                            ? $state->label()
                            : SourceType::tryFrom((string) $state)?->label() ?? $state)
                    ->badge(),

                TextColumn::make('work_mode')
                    ->label(__('dashboard.widgets.recent_applications.work_mode'))
                    ->formatStateUsing(fn (WorkMode|string|null $state): ?string => $state instanceof WorkMode
                            ? $state->label()
                            : WorkMode::tryFrom((string) $state)?->label() ?? $state)
                    ->badge(),

                TextColumn::make('sent_at')
                    ->label(__('dashboard.widgets.recent_applications.sent_at'))
                    ->date('d/m/Y')
                    ->sortable(),
            ]);
    }
}
