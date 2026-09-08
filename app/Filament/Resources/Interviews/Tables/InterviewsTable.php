<?php

namespace App\Filament\Resources\Interviews\Tables;

use App\Enums\InterviewResult;
use App\Enums\InterviewType;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InterviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jobApplication.company.name')
                    ->label(__('companies.model_label'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jobApplication.job_title')
                    ->label(__('job-applications.fields.job_title'))
                    ->searchable()
                    ->wrap()
                    ->lineClamp(2)
                    ->limit(45),

                TextColumn::make('interview_at')
                    ->label(__('interviews.fields.interview_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->wrapHeader(),

                TextColumn::make('interview_type')
                    ->label(__('interviews.fields.interview_type'))
                    ->formatStateUsing(fn (InterviewType|string|null $state): ?string => $state instanceof InterviewType
                            ? $state->label()
                            : InterviewType::tryFrom($state ?? '')?->label() ?? $state)
                    ->badge()
                    ->sortable(),

                TextColumn::make('result')
                    ->label(__('interviews.fields.result'))
                    ->formatStateUsing(fn (InterviewResult|string|null $state): ?string => $state instanceof InterviewResult
                            ? $state->label()
                            : InterviewResult::tryFrom($state ?? '')?->label() ?? $state)
                    ->badge()
                    ->color(fn (InterviewResult|string|null $state): string => $state instanceof InterviewResult
                            ? $state->color()
                            : InterviewResult::tryFrom($state ?? '')?->color() ?? 'info')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('interviews.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('interviews.fields.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('interview_type')
                    ->label(__('interviews.fields.interview_type'))
                    ->options(InterviewType::options()),

                SelectFilter::make('result')
                    ->label(__('interviews.fields.result'))
                    ->options(InterviewResult::options()),

                Filter::make('interview_at')
                    ->label(__('interviews.fields.interview_at'))
                    ->schema([
                        DatePicker::make('interview_from')
                            ->label(__('interviews.fields.interview_from')),

                        DatePicker::make('interview_until')
                            ->label(__('interviews.fields.interview_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['interview_from'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('interview_at', '>=', $date),
                            )
                            ->when(
                                $data['interview_until'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('interview_at', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
