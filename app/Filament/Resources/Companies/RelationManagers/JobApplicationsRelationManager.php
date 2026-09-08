<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Enums\ApplicationStatus;
use App\Filament\Resources\JobApplications\JobApplicationResource;
use App\Models\JobApplication;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class JobApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'jobApplications';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('job-applications.plural_model_label');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('job_title')
            ->defaultSort('sent_at', 'desc')
            ->columns([
                TextColumn::make('job_title')
                    ->label(__('job-applications.fields.job_title'))
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('status')
                    ->label(__('job-applications.fields.status'))
                    ->formatStateUsing(
                        fn (ApplicationStatus|string|null $state): ?string => $state instanceof ApplicationStatus
                            ? $state->label()
                            : ApplicationStatus::tryFrom($state ?? '')?->label() ?? $state,
                    )
                    ->badge()
                    ->color(
                        fn (ApplicationStatus|string|null $state): string => $state instanceof ApplicationStatus
                            ? $state->color()
                            : ApplicationStatus::tryFrom($state ?? '')?->color() ?? 'info',
                    )
                    ->sortable(),

                TextColumn::make('sent_at')
                    ->label(__('job-applications.fields.sent_at'))
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('next_action_at')
                    ->label(__('job-applications.fields.next_action_at'))
                    ->date('d/m/Y')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('viewApplication')
                    ->label(__('job-applications.actions.view'))
                    ->icon('heroicon-o-eye')
                    ->url(
                        fn (JobApplication $record): string => JobApplicationResource::getUrl(
                            'view',
                            ['record' => $record],
                        ),
                    ),
            ]);
    }
}
