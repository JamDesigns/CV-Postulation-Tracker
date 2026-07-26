<?php
namespace App\Filament\Widgets;

use App\Enums\ApplicationStatus;
use App\Enums\NextActionUrgency;
use App\Models\JobApplication;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingNextStepsTable extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->header(view('filament.widgets.pending-next-steps-table-header'))
            ->emptyStateHeading(__('dashboard.widgets.pending_next_steps.empty'))
            ->query(
                JobApplication::query()
                    ->whereNotNull('next_step', 'and')
                    ->where('next_step', '<>', '', 'and')
                    ->whereNotIn('status', [
                        ApplicationStatus::Rejected->value,
                        ApplicationStatus::Hired->value,
                        ApplicationStatus::Paused->value,
                    ], 'and')
                    ->orderByRaw('next_action_at IS NULL', [])
                    ->orderBy('next_action_at', 'asc')
                    ->latest('sent_at')
            )
            ->filters([
                SelectFilter::make('next_action_urgency')
                    ->label(__('dashboard.widgets.pending_next_steps.urgency'))
                    ->options(NextActionUrgency::options())
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        return match ($value) {
                            NextActionUrgency::Overdue->value  => $query->where('next_action_at', '<', today(), 'and'),
                            NextActionUrgency::Today->value    => $query->whereDate('next_action_at', '=', today(), 'and'),
                            NextActionUrgency::Upcoming->value => $query->where('next_action_at', '>', today(), 'and'),
                            NextActionUrgency::NoDate->value   => $query->whereNull('next_action_at', 'and'),
                            default                            => $query,
                        };
                    }),
            ])
            ->columns([
                TextColumn::make('company_name')
                    ->label(__('dashboard.widgets.pending_next_steps.company'))
                    ->searchable()
                    ->sortable()
                    ->limit(35)
                    ->wrap(),

                TextColumn::make('job_title')
                    ->label(__('dashboard.widgets.pending_next_steps.position'))
                    ->searchable()
                    ->limit(35)
                    ->wrap(),

                TextColumn::make('status')
                    ->label(__('dashboard.widgets.pending_next_steps.status'))
                    ->formatStateUsing(fn(ApplicationStatus | string | null $state): ?string => $state instanceof ApplicationStatus
                            ? $state->label()
                            : ApplicationStatus::tryFrom((string) $state)?->label() ?? $state)
                    ->badge()
                    ->color(fn(ApplicationStatus | string | null $state): string => $state instanceof ApplicationStatus
                            ? $state->color()
                            : ApplicationStatus::tryFrom((string) $state)?->color() ?? 'info'),

                TextColumn::make('next_step')
                    ->label(__('dashboard.widgets.pending_next_steps.next_step'))
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('next_action_at')
                    ->label(__('dashboard.widgets.pending_next_steps.next_action_at'))
                    ->date('d/m/Y')
                    ->badge()
                    ->color(fn($record): string => $record->nextActionUrgencyColor())
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('sent_at')
                    ->label(__('dashboard.widgets.pending_next_steps.sent_at'))
                    ->date('d/m/Y')
                    ->sortable(),
            ]);
    }
}
