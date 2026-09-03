<?php

namespace App\Filament\Resources\Interviews\Schemas;

use App\Enums\InterviewResult;
use App\Enums\InterviewType;
use App\Models\Interview;
use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

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
            Tabs::make(__('interviews.sections.form'))
                ->tabs([
                    Tabs\Tab::make(__('interviews.sections.main'))
                        ->schema([
                            ...($includeJobApplicationSelect ? [
                                Select::make('job_application_id')
                                    ->label(__('interviews.fields.job_application_id'))
                                    ->relationship('jobApplication', 'job_title')
                                    ->getOptionLabelFromRecordUsing(
                                        fn ($record): string => "{$record->company_name} - {$record->job_title}"
                                    )
                                    ->searchable(['company_name', 'job_title'])
                                    ->preload()
                                    ->required(),
                            ] : []),

                            DateTimePicker::make('interview_at')
                                ->label(__('interviews.fields.interview_at'))
                                ->seconds(false)
                                ->rules([
                                    fn (Get $get, $record, $livewire): Closure => function (
                                        string $attribute,
                                        $value,
                                        Closure $fail
                                    ) use ($get, $record, $livewire): void {
                                        $jobApplicationId = $livewire instanceof RelationManager
                                            ? $livewire->getOwnerRecord()->getKey()
                                            : $get('job_application_id');

                                        $interviewType = $get('interview_type');

                                        if (blank($jobApplicationId) || blank($interviewType) || blank($value)) {
                                            return;
                                        }

                                        $query = Interview::query()
                                            ->where('job_application_id', $jobApplicationId)
                                            ->where('interview_type', $interviewType)
                                            ->where(
                                                'interview_at',
                                                Carbon::parse($value)->format('Y-m-d H:i:s'),
                                            );

                                        if ($record !== null) {
                                            $query->whereKeyNot($record->getKey());
                                        }

                                        if ($query->exists()) {
                                            $fail(__('interviews.validation.duplicate'));
                                        }
                                    },
                                ]),

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
                                ->autosize()
                                ->columnSpanFull(),

                            Textarea::make('expected_questions')
                                ->label(__('interviews.fields.expected_questions'))
                                ->rows(3)
                                ->autosize(),

                            Textarea::make('strengths_to_defend')
                                ->label(__('interviews.fields.strengths_to_defend'))
                                ->rows(3)
                                ->autosize(),

                            Textarea::make('risks_to_clarify')
                                ->label(__('interviews.fields.risks_to_clarify'))
                                ->rows(3)
                                ->autosize()
                                ->columnSpanFull(),
                        ]),

                    Tabs\Tab::make(__('interviews.sections.result'))
                        ->schema([
                            Textarea::make('notes')
                                ->label(__('interviews.fields.notes'))
                                ->rows(4)
                                ->autosize()
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull(),
        ];
    }
}
