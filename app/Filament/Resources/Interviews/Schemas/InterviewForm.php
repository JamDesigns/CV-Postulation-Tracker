<?php
namespace App\Filament\Resources\Interviews\Schemas;

use App\Enums\InterviewResult;
use App\Enums\InterviewType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class InterviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::components());
    }

    public static function components(bool $includeJobApplicationSelect = true): array
    {
        return [
            Tabs::make('Interview form')
                ->tabs([
                    Tabs\Tab::make(__('interviews.sections.main'))
                        ->schema([
                             ...($includeJobApplicationSelect ? [
                                Select::make('job_application_id')
                                    ->label(__('interviews.fields.job_application_id'))
                                    ->relationship('jobApplication', 'job_title')
                                    ->getOptionLabelFromRecordUsing(
                                        fn($record) : string => "{$record->company_name} - {$record->job_title}"
                                    )
                                    ->searchable(['company_name', 'job_title'])
                                    ->preload()
                                    ->required(),
                            ]:[]),

                            DateTimePicker::make('interview_at')
                                ->label(__('interviews.fields.interview_at'))
                                ->seconds(false),

                            Select::make('interview_type')
                                ->label(__('interviews.fields.interview_type'))
                                ->options(InterviewType::options())
                                ->default(InterviewType::Hr->value)
                                ->required(),

                            Select::make('result')
                                ->label(__('interviews.fields.result'))
                                ->options(InterviewResult::options())
                                ->default(InterviewResult::Pending->value)
                                ->required(),
                        ])
                        ->columns(2),

                    Tabs\Tab::make(__('interviews.sections.preparation'))
                        ->schema([
                            Textarea::make('people')
                                ->label(__('interviews.fields.people'))
                                ->rows(3)
                                ->extraInputAttributes(['style' => 'min-height: 5.5rem; resize: vertical;'])
                                ->columnSpanFull(),

                            Textarea::make('expected_questions')
                                ->label(__('interviews.fields.expected_questions'))
                                ->rows(3)
                                ->extraInputAttributes(['style' => 'min-height: 5.5rem; resize: vertical;']),

                            Textarea::make('strengths_to_defend')
                                ->label(__('interviews.fields.strengths_to_defend'))
                                ->rows(3)
                                ->extraInputAttributes(['style' => 'min-height: 5.5rem; resize: vertical;']),

                            Textarea::make('risks_to_clarify')
                                ->label(__('interviews.fields.risks_to_clarify'))
                                ->rows(3)
                                ->extraInputAttributes(['style' => 'min-height: 5.5rem; resize: vertical;'])
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    Tabs\Tab::make(__('interviews.sections.result'))
                        ->schema([
                            Textarea::make('notes')
                                ->label(__('interviews.fields.notes'))
                                ->rows(4)
                                ->extraInputAttributes(['style' => 'min-height: 7rem; resize: vertical;'])
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull(),
        ];
    }
}
