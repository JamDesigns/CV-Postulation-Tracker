<?php

namespace App\Filament\Resources\TechnicalAssessments\Tables;

use App\Enums\TechnicalAssessmentDirection;
use App\Enums\TechnicalAssessmentModality;
use App\Enums\TechnicalAssessmentResult;
use App\Enums\TechnicalAssessmentStatus;
use App\Enums\TechnicalAssessmentType;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TechnicalAssessmentsTable
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

                TextColumn::make('test_type')
                    ->label(__('technical-assessments.fields.test_type'))
                    ->formatStateUsing(
                        fn (TechnicalAssessmentType|string|null $state): ?string => $state instanceof TechnicalAssessmentType
                                ? $state->label()
                                : TechnicalAssessmentType::tryFrom($state ?? '')?->label()
                    )
                    ->badge()
                    ->sortable(),

                TextColumn::make('modality')
                    ->label(__('technical-assessments.fields.modality'))
                    ->formatStateUsing(
                        fn (TechnicalAssessmentModality|string|null $state): ?string => $state instanceof TechnicalAssessmentModality
                                ? $state->label()
                                : TechnicalAssessmentModality::tryFrom($state ?? '')?->label()
                    )
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('direction')
                    ->label(__('technical-assessments.fields.direction'))
                    ->formatStateUsing(
                        fn (TechnicalAssessmentDirection|string|null $state): ?string => $state instanceof TechnicalAssessmentDirection
                                ? $state->label()
                                : TechnicalAssessmentDirection::tryFrom($state ?? '')?->label()
                    )
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label(__('technical-assessments.fields.status'))
                    ->formatStateUsing(
                        fn (TechnicalAssessmentStatus|string|null $state): ?string => $state instanceof TechnicalAssessmentStatus
                                ? $state->label()
                                : TechnicalAssessmentStatus::tryFrom($state ?? '')?->label()
                    )
                    ->badge()
                    ->color(
                        fn (TechnicalAssessmentStatus|string|null $state): string => $state instanceof TechnicalAssessmentStatus
                                ? $state->color()
                                : TechnicalAssessmentStatus::tryFrom($state ?? '')?->color() ?? 'info'
                    )
                    ->sortable(),

                TextColumn::make('result')
                    ->label(__('technical-assessments.fields.result'))
                    ->formatStateUsing(
                        fn (TechnicalAssessmentResult|string|null $state): ?string => $state instanceof TechnicalAssessmentResult
                                ? $state->label()
                                : TechnicalAssessmentResult::tryFrom($state ?? '')?->label()
                    )
                    ->badge()
                    ->color(
                        fn (TechnicalAssessmentResult|string|null $state): string => $state instanceof TechnicalAssessmentResult
                                ? $state->color()
                                : TechnicalAssessmentResult::tryFrom($state ?? '')?->color() ?? 'info'
                    )
                    ->placeholder('-'),

                TextColumn::make('scheduled_at')
                    ->label(__('technical-assessments.fields.scheduled_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('deadline_at')
                    ->label(__('technical-assessments.fields.deadline_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('received_at')
                    ->label(__('technical-assessments.fields.received_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('finished_at')
                    ->label(__('technical-assessments.fields.finished_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('technical-assessments.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('technical-assessments.fields.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('test_type')
                    ->label(__('technical-assessments.fields.test_type'))
                    ->options(TechnicalAssessmentType::options()),

                SelectFilter::make('modality')
                    ->label(__('technical-assessments.fields.modality'))
                    ->options(TechnicalAssessmentModality::options()),

                SelectFilter::make('direction')
                    ->label(__('technical-assessments.fields.direction'))
                    ->options(TechnicalAssessmentDirection::options()),

                SelectFilter::make('status')
                    ->label(__('technical-assessments.fields.status'))
                    ->options(TechnicalAssessmentStatus::options()),

                SelectFilter::make('result')
                    ->label(__('technical-assessments.fields.result'))
                    ->options(TechnicalAssessmentResult::options()),
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
