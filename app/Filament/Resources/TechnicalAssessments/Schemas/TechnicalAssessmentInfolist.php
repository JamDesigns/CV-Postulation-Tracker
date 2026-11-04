<?php

namespace App\Filament\Resources\TechnicalAssessments\Schemas;

use App\Enums\TechnicalAssessmentDirection;
use App\Enums\TechnicalAssessmentModality;
use App\Enums\TechnicalAssessmentResult;
use App\Enums\TechnicalAssessmentStatus;
use App\Enums\TechnicalAssessmentType;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class TechnicalAssessmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make(__('technical-assessments.sections.details'))
                    ->tabs([
                        Tabs\Tab::make(__('technical-assessments.sections.main'))
                            ->schema([
                                TextEntry::make('jobApplication.company.name')
                                    ->label(__('companies.model_label'))
                                    ->placeholder('-'),

                                TextEntry::make('jobApplication.job_title')
                                    ->label(__('job-applications.fields.job_title'))
                                    ->placeholder('-'),

                                TextEntry::make('test_type')
                                    ->label(__('technical-assessments.fields.test_type'))
                                    ->formatStateUsing(
                                        fn (TechnicalAssessmentType|string|null $state): ?string => $state instanceof TechnicalAssessmentType
                                                ? $state->label()
                                                : TechnicalAssessmentType::tryFrom($state ?? '')?->label()
                                    )
                                    ->badge(),

                                TextEntry::make('modality')
                                    ->label(__('technical-assessments.fields.modality'))
                                    ->formatStateUsing(
                                        fn (TechnicalAssessmentModality|string|null $state): ?string => $state instanceof TechnicalAssessmentModality
                                                ? $state->label()
                                                : TechnicalAssessmentModality::tryFrom($state ?? '')?->label()
                                    )
                                    ->visible(
                                        fn ($record): bool => $record?->test_type === TechnicalAssessmentType::Live
                                    ),

                                TextEntry::make('direction')
                                    ->label(__('technical-assessments.fields.direction'))
                                    ->formatStateUsing(
                                        fn (TechnicalAssessmentDirection|string|null $state): ?string => $state instanceof TechnicalAssessmentDirection
                                                ? $state->label()
                                                : TechnicalAssessmentDirection::tryFrom($state ?? '')?->label()
                                    )
                                    ->badge(),

                                TextEntry::make('status')
                                    ->label(__('technical-assessments.fields.status'))
                                    ->formatStateUsing(
                                        fn (TechnicalAssessmentStatus|string|null $state): ?string => $state instanceof TechnicalAssessmentStatus
                                                ? $state->label()
                                                : TechnicalAssessmentStatus::tryFrom($state ?? '')?->label()
                                    )
                                    ->badge()
                                    ->color(
                                        fn ($record): string => $record?->status?->color() ?? 'info'
                                    ),

                                TextEntry::make('result')
                                    ->label(__('technical-assessments.fields.result'))
                                    ->formatStateUsing(
                                        fn (TechnicalAssessmentResult|string|null $state): ?string => $state instanceof TechnicalAssessmentResult
                                                ? $state->label()
                                                : TechnicalAssessmentResult::tryFrom($state ?? '')?->label()
                                    )
                                    ->badge()
                                    ->color(
                                        fn ($record): string => $record?->result?->color() ?? 'info'
                                    )
                                    ->visible(
                                        fn ($record): bool => $record?->status === TechnicalAssessmentStatus::Finished
                                    ),
                            ])
                            ->columns(2),

                        Tabs\Tab::make(__('technical-assessments.sections.dates'))
                            ->schema([
                                TextEntry::make('received_at')
                                    ->label(__('technical-assessments.fields.received_at'))
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),

                                TextEntry::make('scheduled_at')
                                    ->label(__('technical-assessments.fields.scheduled_at'))
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-')
                                    ->visible(
                                        fn ($record): bool => $record?->test_type === TechnicalAssessmentType::Live
                                    ),

                                TextEntry::make('deadline_at')
                                    ->label(__('technical-assessments.fields.deadline_at'))
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-')
                                    ->visible(
                                        fn ($record): bool => $record?->test_type === TechnicalAssessmentType::AtHome
                                    ),

                                TextEntry::make('finished_at')
                                    ->label(__('technical-assessments.fields.finished_at'))
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-')
                                    ->visible(
                                        fn ($record): bool => $record?->status === TechnicalAssessmentStatus::Finished
                                    ),

                                TextEntry::make('result_communicated_at')
                                    ->label(__('technical-assessments.fields.result_communicated_at'))
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-')
                                    ->visible(
                                        fn ($record): bool => $record?->status === TechnicalAssessmentStatus::Finished
                                            && in_array($record?->result, [
                                                TechnicalAssessmentResult::Passed,
                                                TechnicalAssessmentResult::NotPassed,
                                            ], true)
                                    ),
                            ])
                            ->columns(2),

                        Tabs\Tab::make(__('technical-assessments.sections.documentation'))
                            ->schema([
                                TextEntry::make('source_url')
                                    ->label(__('technical-assessments.fields.source_url'))
                                    ->url(fn (?string $state): ?string => $state)
                                    ->openUrlInNewTab()
                                    ->placeholder('-'),

                                TextEntry::make('delivery_url')
                                    ->label(__('technical-assessments.fields.delivery_url'))
                                    ->url(fn (?string $state): ?string => $state)
                                    ->openUrlInNewTab()
                                    ->placeholder('-'),

                                TextEntry::make('notes')
                                    ->label(__('technical-assessments.fields.notes'))
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tabs\Tab::make(__('technical-assessments.sections.metadata'))
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label(__('technical-assessments.fields.created_at'))
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),

                                TextEntry::make('updated_at')
                                    ->label(__('technical-assessments.fields.updated_at'))
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
