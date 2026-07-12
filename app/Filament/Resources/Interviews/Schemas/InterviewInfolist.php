<?php

namespace App\Filament\Resources\Interviews\Schemas;

use App\Enums\InterviewResult;
use App\Enums\InterviewType;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class InterviewInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Interview details')
                    ->tabs([
                        Tabs\Tab::make(__('interviews.sections.main'))
                            ->schema([
                                TextEntry::make('jobApplication.company_name')
                                    ->label(__('job-applications.fields.company_name'))
                                    ->placeholder('-'),

                                TextEntry::make('jobApplication.job_title')
                                    ->label(__('job-applications.fields.job_title'))
                                    ->placeholder('-'),

                                TextEntry::make('interview_at')
                                    ->label(__('interviews.fields.interview_at'))
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),

                                TextEntry::make('interview_type')
                                    ->label(__('interviews.fields.interview_type'))
                                    ->formatStateUsing(fn(InterviewType|string|null $state): ?string => $state instanceof InterviewType
                                        ? $state->label()
                                        : InterviewType::tryFrom($state ?? '')?->label() ?? $state)
                                    ->badge(),

                                TextEntry::make('result')
                                    ->label(__('interviews.fields.result'))
                                    ->formatStateUsing(fn(InterviewResult|string|null $state): ?string => $state instanceof InterviewResult
                                        ? $state->label()
                                        : InterviewResult::tryFrom($state ?? '')?->label() ?? $state)
                                    ->badge(),
                            ])
                            ->columns(2),

                        Tabs\Tab::make(__('interviews.sections.preparation'))
                            ->schema([
                                TextEntry::make('people')
                                    ->label(__('interviews.fields.people'))
                                    ->placeholder('-')
                                    ->columnSpanFull(),

                                TextEntry::make('expected_questions')
                                    ->label(__('interviews.fields.expected_questions'))
                                    ->placeholder('-')
                                    ->columnSpanFull(),

                                TextEntry::make('strengths_to_defend')
                                    ->label(__('interviews.fields.strengths_to_defend'))
                                    ->placeholder('-')
                                    ->columnSpanFull(),

                                TextEntry::make('risks_to_clarify')
                                    ->label(__('interviews.fields.risks_to_clarify'))
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ]),

                        Tabs\Tab::make(__('interviews.sections.result'))
                            ->schema([
                                TextEntry::make('notes')
                                    ->label(__('interviews.fields.notes'))
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ]),

                        Tabs\Tab::make('Metadatos')
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Creado')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),

                                TextEntry::make('updated_at')
                                    ->label('Actualizado')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
