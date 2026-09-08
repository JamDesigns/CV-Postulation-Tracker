<?php

namespace App\Filament\Resources\JobApplications\Tables;

use App\Enums\ApplicationStatus;
use App\Enums\Currency;
use App\Enums\JobApplicationEventType;
use App\Enums\NextActionUrgency;
use App\Enums\SourceType;
use App\Enums\WorkMode;
use App\Models\JobApplication;
use App\Support\CurrencyFormatter;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JobApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label(__('companies.model_label'))
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->lineClamp(3),

                TextColumn::make('job_title')
                    ->label(__('job-applications.fields.job_title'))
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->lineClamp(3)
                    ->limit(50),

                TextColumn::make('status')
                    ->label(__('job-applications.fields.status'))
                    ->formatStateUsing(fn (ApplicationStatus|string|null $state): ?string => $state instanceof ApplicationStatus
                            ? $state->label()
                            : ApplicationStatus::tryFrom($state ?? '')?->label() ?? $state)
                    ->badge()
                    ->color(fn (ApplicationStatus|string|null $state): string => $state instanceof ApplicationStatus
                            ? $state->color()
                            : ApplicationStatus::tryFrom($state ?? '')?->color() ?? 'info')
                    ->sortable(),

                TextColumn::make('sent_at')
                    ->label(__('job-applications.fields.sent_at'))
                    ->wrapHeader()
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('next_action_at')
                    ->label(__('job-applications.fields.next_action_at'))
                    ->wrapHeader()
                    ->date('d/m/Y')
                    ->badge()
                    ->color(fn (JobApplication $record): string => $record->nextActionUrgencyColor())
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('source')
                    ->label(__('job-applications.fields.source'))
                    ->formatStateUsing(fn (SourceType|string|null $state): ?string => $state instanceof SourceType
                            ? $state->label()
                            : SourceType::tryFrom($state ?? '')?->label() ?? $state)
                    ->badge()
                    ->sortable()
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('work_mode')
                    ->label(__('job-applications.fields.work_mode'))
                    ->formatStateUsing(fn (WorkMode|string|null $state): ?string => $state instanceof WorkMode
                            ? $state->label()
                            : WorkMode::tryFrom($state ?? '')?->label() ?? $state)
                    ->badge()
                    ->sortable(),

                TextColumn::make('location')
                    ->label(__('job-applications.fields.location'))
                    ->searchable()
                    ->wrap()
                    ->lineClamp(2),

                TextColumn::make('salary')
                    ->label(__('job-applications.fields.salary'))
                    ->state(function (JobApplication $record): ?string {
                        if ($record->salary_min === null) {
                            return null;
                        }

                        $currency = $record->currency instanceof Currency
                            ? $record->currency
                            : Currency::tryFrom((string) $record->currency);

                        if ($currency === null) {
                            return $record->salary_max === null
                                ? (string) $record->salary_min
                                : "{$record->salary_min} - {$record->salary_max}";
                        }

                        $salaryMin = CurrencyFormatter::format(
                            $record->salary_min,
                            $currency,
                        );

                        if ($record->salary_max === null) {
                            return $salaryMin;
                        }

                        $salaryMax = CurrencyFormatter::format(
                            $record->salary_max,
                            $currency,
                        );

                        return "{$salaryMin} - {$salaryMax}";
                    })
                    ->wrap()
                    ->placeholder('-')
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('salary_min', $direction)),

                TextColumn::make('cvVersion.name')
                    ->label(__('job-applications.fields.cv_version_id'))
                    ->searchable()
                    ->wrap()
                    ->lineClamp(3)
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cv_pdf_file')
                    ->label(__('cv-versions.fields.pdf_path'))
                    ->state(fn ($record): ?string => $record->cvVersion?->pdf_path ? $record->cvVersion->pdfFriendlyName() : null)
                    ->placeholder('-')
                    ->icon(fn ($record): ?Heroicon => $record->cvVersion?->pdf_path ? Heroicon::ArrowTopRightOnSquare : null)
                    ->iconColor('primary')
                    ->url(fn ($record): ?string => $record->cvVersion?->pdf_path ? route('filament.admin.cv-versions.pdf', $record->cvVersion) : null)
                    ->openUrlInNewTab()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cv_docx_file')
                    ->label(__('cv-versions.fields.docx_path'))
                    ->state(fn ($record): ?string => $record->cvVersion?->docx_path ? $record->cvVersion->docxFriendlyName() : null)
                    ->placeholder('-')
                    ->icon(fn ($record): ?Heroicon => $record->cvVersion?->docx_path ? Heroicon::ArrowDownTray : null)
                    ->iconColor('primary')
                    ->url(fn ($record): ?string => $record->cvVersion?->docx_path ? route('filament.admin.cv-versions.docx', $record->cvVersion) : null)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('job-applications.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('job-applications.fields.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('job-applications.fields.status'))
                    ->options(ApplicationStatus::options()),

                SelectFilter::make('next_action_urgency')
                    ->label(__('job-applications.fields.next_action_urgency'))
                    ->options(NextActionUrgency::options())
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        return match ($value) {
                            NextActionUrgency::Overdue->value => $query->where('next_action_at', '<', today(), 'and'),
                            NextActionUrgency::Today->value => $query->whereDate('next_action_at', '=', today(), 'and'),
                            NextActionUrgency::Upcoming->value => $query->where('next_action_at', '>', today(), 'and'),
                            NextActionUrgency::NoDate->value => $query->whereNull('next_action_at', 'and'),
                            default => $query,
                        };
                    }),

                SelectFilter::make('source')
                    ->label(__('job-applications.fields.source'))
                    ->options(SourceType::options()),

                SelectFilter::make('work_mode')
                    ->label(__('job-applications.fields.work_mode'))
                    ->options(WorkMode::options()),

                TernaryFilter::make('dossier_sent')
                    ->label(__('job-applications.fields.dossier_sent')),
            ])
            ->defaultSort('sent_at', 'desc')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),

                    EditAction::make(),

                    Action::make('markAsSent')
                        ->label(__('job-applications.actions.mark_as_sent'))
                        ->icon(Heroicon::PaperAirplane)
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn ($record): bool => $record->status === ApplicationStatus::Pending
                            && $record->cv_version_id !== null)
                        ->action(function ($record): void {
                            $previousStatus = $record->status;
                            $locale = app()->getLocale();
                            $nextStep = __(
                                'job-applications.quick_actions.next_steps.send_follow_up',
                                [],
                                $locale,
                            );
                            $nextActionAt = today()->addDays(7);

                            $record->forceFill([
                                'status' => ApplicationStatus::Sent->value,
                                'sent_at' => today(),
                                'next_step' => $nextStep,
                                'next_action_at' => $nextActionAt,
                            ])->save();

                            self::createApplicationEvent(
                                $record,
                                JobApplicationEventType::ApplicationSent,
                                __(
                                    'job-application-events.types.application_sent',
                                    [],
                                    $locale,
                                ),
                                $nextStep,
                                $previousStatus,
                                ApplicationStatus::Sent,
                                $nextActionAt,
                            );
                        }),

                    Action::make('markAsResponded')
                        ->label(__('job-applications.actions.mark_as_responded'))
                        ->icon(Heroicon::ChatBubbleLeftRight)
                        ->color('info')
                        ->requiresConfirmation()
                        ->visible(fn ($record): bool => in_array($record->status, [
                            ApplicationStatus::Sent,
                            ApplicationStatus::FollowUpSent,
                        ], true))
                        ->action(function ($record): void {
                            $previousStatus = $record->status;
                            $locale = app()->getLocale();
                            $nextStep = __(
                                'job-applications.quick_actions.next_steps.review_response',
                                [],
                                $locale,
                            );
                            $nextActionAt = today()->addDays(2);

                            $record->forceFill([
                                'status' => ApplicationStatus::Responded->value,
                                'next_step' => $nextStep,
                                'next_action_at' => $nextActionAt,
                            ])->save();

                            self::createApplicationEvent(
                                $record,
                                JobApplicationEventType::ResponseReceived,
                                __(
                                    'job-application-events.types.response_received',
                                    [],
                                    $locale,
                                ),
                                $nextStep,
                                $previousStatus,
                                ApplicationStatus::Responded,
                                $nextActionAt,
                            );
                        }),

                    Action::make('markAsTechnicalTest')
                        ->label(__('job-applications.actions.mark_as_technical_test'))
                        ->icon(Heroicon::CodeBracketSquare)
                        ->color('primary')
                        ->schema([
                            Textarea::make('next_step')
                                ->label(__('job-applications.quick_actions.fields.next_step'))
                                ->default(fn ($record): string => __(
                                    'job-applications.quick_actions.next_steps.complete_technical_test',
                                    [],
                                    app()->getLocale(),
                                ))
                                ->required()
                                ->rows(3)
                                ->autosize(),

                            DatePicker::make('next_action_at')
                                ->label(__('job-applications.quick_actions.fields.next_action_at'))
                                ->default(today()->addDays(7)),

                            Textarea::make('notes_to_append')
                                ->label(__('job-applications.quick_actions.fields.notes_to_append'))
                                ->rows(5)
                                ->autosize(),
                        ])
                        ->visible(fn ($record): bool => in_array($record->status, [
                            ApplicationStatus::Sent,
                            ApplicationStatus::Responded,
                            ApplicationStatus::Interview,
                            ApplicationStatus::FollowUpSent,
                        ], true))
                        ->modalSubmitAction(fn (Action $action): Action => $action->color('primary'))
                        ->action(function ($record, array $data): void {
                            $previousStatus = $record->status;
                            $locale = app()->getLocale();

                            $nextStep = trim((string) ($data['next_step'] ?? ''));
                            $notes = trim((string) ($data['notes_to_append'] ?? ''));
                            $nextActionAt = $data['next_action_at'] ?? null;

                            $record->forceFill([
                                'status' => ApplicationStatus::TechnicalTest->value,
                                'next_step' => $nextStep,
                                'next_action_at' => $nextActionAt,
                            ])->save();

                            self::createApplicationEvent(
                                $record,
                                JobApplicationEventType::TechnicalTest,
                                __(
                                    'job-application-events.types.technical_test',
                                    [],
                                    $locale,
                                ),
                                self::eventBody(null, $notes),
                                $previousStatus,
                                ApplicationStatus::TechnicalTest,
                                $nextActionAt,
                            );
                        }),

                    Action::make('markFollowUpSent')
                        ->label(__('job-applications.actions.mark_follow_up_sent'))
                        ->icon(Heroicon::PaperAirplane)
                        ->color('warning')
                        ->schema([
                            Textarea::make('next_step')
                                ->label(__('job-applications.quick_actions.fields.next_step'))
                                ->default(fn ($record): string => __(
                                    'job-applications.quick_actions.next_steps.wait_after_follow_up',
                                    [],
                                    app()->getLocale(),
                                ))
                                ->required()
                                ->rows(3)
                                ->autosize(),

                            DatePicker::make('next_action_at')
                                ->label(__('job-applications.quick_actions.fields.next_action_at'))
                                ->default(today()->addDays(7)),

                            Textarea::make('notes_to_append')
                                ->label(__('job-applications.quick_actions.fields.notes_to_append'))
                                ->rows(5)
                                ->autosize(),
                        ])
                        ->visible(fn ($record): bool => in_array($record->status, [
                            ApplicationStatus::Sent,
                            ApplicationStatus::Responded,
                            ApplicationStatus::Interview,
                            ApplicationStatus::TechnicalTest,
                            ApplicationStatus::FollowUpSent,
                        ], true))
                        ->modalSubmitAction(fn (Action $action): Action => $action->color('primary'))
                        ->action(function ($record, array $data): void {
                            $previousStatus = $record->status;
                            $locale = app()->getLocale();

                            $nextStep = trim((string) ($data['next_step'] ?? ''));
                            $notes = trim((string) ($data['notes_to_append'] ?? ''));
                            $nextActionAt = $data['next_action_at'] ?? null;

                            $record->forceFill([
                                'status' => ApplicationStatus::FollowUpSent->value,
                                'next_step' => $nextStep,
                                'next_action_at' => $nextActionAt,
                            ])->save();

                            self::createApplicationEvent(
                                $record,
                                JobApplicationEventType::FollowUpSent,
                                __(
                                    'job-application-events.types.follow_up_sent',
                                    [],
                                    $locale,
                                ),
                                self::eventBody(null, $notes),
                                $previousStatus,
                                ApplicationStatus::FollowUpSent,
                                $nextActionAt,
                            );
                        }),

                    Action::make('pauseApplication')
                        ->label(__('job-applications.actions.pause_application'))
                        ->icon(Heroicon::PauseCircle)
                        ->color('gray')
                        ->schema([
                            Textarea::make('next_step')
                                ->label(__('job-applications.quick_actions.fields.next_step'))
                                ->default(fn ($record): string => __(
                                    'job-applications.quick_actions.next_steps.review_paused_application',
                                    [],
                                    app()->getLocale(),
                                ))
                                ->required()
                                ->rows(3)
                                ->autosize(),

                            DatePicker::make('next_action_at')
                                ->label(__('job-applications.quick_actions.fields.next_action_at')),

                            Textarea::make('notes_to_append')
                                ->label(__('job-applications.quick_actions.fields.notes_to_append'))
                                ->rows(5)
                                ->autosize(),
                        ])
                        ->visible(fn ($record): bool => in_array($record->status, [
                            ApplicationStatus::Pending,
                            ApplicationStatus::Sent,
                            ApplicationStatus::Responded,
                            ApplicationStatus::Interview,
                            ApplicationStatus::TechnicalTest,
                            ApplicationStatus::FollowUpSent,
                        ], true))
                        ->modalSubmitAction(fn (Action $action): Action => $action->color('primary'))
                        ->action(function ($record, array $data): void {
                            $previousStatus = $record->status;
                            $locale = app()->getLocale();

                            $nextStep = trim((string) ($data['next_step'] ?? ''));
                            $notes = trim((string) ($data['notes_to_append'] ?? ''));
                            $nextActionAt = $data['next_action_at'] ?? null;

                            $record->forceFill([
                                'status' => ApplicationStatus::Paused->value,
                                'next_step' => $nextStep,
                                'next_action_at' => $nextActionAt,
                            ])->save();

                            self::createApplicationEvent(
                                $record,
                                JobApplicationEventType::Paused,
                                __(
                                    'job-application-events.types.paused',
                                    [],
                                    $locale,
                                ),
                                self::eventBody(null, $notes),
                                $previousStatus,
                                ApplicationStatus::Paused,
                                $nextActionAt,
                            );
                        }),

                    Action::make('rejectApplication')
                        ->label(__('job-applications.actions.reject_application'))
                        ->icon(Heroicon::XCircle)
                        ->color('danger')
                        ->schema([
                            Textarea::make('notes_to_append')
                                ->label(__('job-applications.quick_actions.fields.notes_to_append'))
                                ->rows(5)
                                ->autosize(),
                        ])
                        ->visible(fn ($record): bool => in_array($record->status, [
                            ApplicationStatus::Pending,
                            ApplicationStatus::Sent,
                            ApplicationStatus::Responded,
                            ApplicationStatus::Interview,
                            ApplicationStatus::TechnicalTest,
                            ApplicationStatus::FollowUpSent,
                            ApplicationStatus::Paused,
                        ], true))
                        ->action(function ($record, array $data): void {
                            $previousStatus = $record->status;
                            $locale = app()->getLocale();
                            $notes = trim((string) ($data['notes_to_append'] ?? ''));

                            $record->forceFill([
                                'status' => ApplicationStatus::Rejected->value,
                                'next_step' => null,
                                'next_action_at' => null,
                            ])->save();

                            self::createApplicationEvent(
                                $record,
                                JobApplicationEventType::Rejected,
                                __(
                                    'job-application-events.types.rejected',
                                    [],
                                    $locale,
                                ),
                                self::eventBody(null, $notes),
                                $previousStatus,
                                ApplicationStatus::Rejected,
                                null,
                            );
                        }),

                    Action::make('markAsHired')
                        ->label(__('job-applications.actions.mark_as_hired'))
                        ->icon(Heroicon::CheckCircle)
                        ->color('success')
                        ->schema([
                            Textarea::make('notes_to_append')
                                ->label(__('job-applications.quick_actions.fields.notes_to_append'))
                                ->rows(5)
                                ->autosize(),
                        ])
                        ->visible(fn ($record): bool => in_array($record->status, [
                            ApplicationStatus::Responded,
                            ApplicationStatus::Interview,
                            ApplicationStatus::TechnicalTest,
                            ApplicationStatus::FollowUpSent,
                        ], true))
                        ->action(function ($record, array $data): void {
                            $previousStatus = $record->status;
                            $locale = app()->getLocale();
                            $notes = trim((string) ($data['notes_to_append'] ?? ''));

                            $record->forceFill([
                                'status' => ApplicationStatus::Hired->value,
                                'next_step' => null,
                                'next_action_at' => null,
                            ])->save();

                            self::createApplicationEvent(
                                $record,
                                JobApplicationEventType::Hired,
                                __(
                                    'job-application-events.types.hired',
                                    [],
                                    $locale,
                                ),
                                self::eventBody(null, $notes),
                                $previousStatus,
                                ApplicationStatus::Hired,
                                null,
                            );
                        }),

                    Action::make('reopenApplication')
                        ->label(__('job-applications.actions.reopen_application'))
                        ->icon(Heroicon::ArrowPath)
                        ->color('primary')
                        ->schema([
                            Select::make('status_to')
                                ->label(__('job-applications.quick_actions.fields.status_to'))
                                ->options([
                                    ApplicationStatus::Pending->value => ApplicationStatus::Pending->label(),
                                    ApplicationStatus::Sent->value => ApplicationStatus::Sent->label(),
                                    ApplicationStatus::Responded->value => ApplicationStatus::Responded->label(),
                                    ApplicationStatus::Interview->value => ApplicationStatus::Interview->label(),
                                    ApplicationStatus::TechnicalTest->value => ApplicationStatus::TechnicalTest->label(),
                                    ApplicationStatus::FollowUpSent->value => ApplicationStatus::FollowUpSent->label(),
                                ])
                                ->default(ApplicationStatus::Sent->value)
                                ->required(),

                            Textarea::make('next_step')
                                ->label(__('job-applications.quick_actions.fields.next_step'))
                                ->default(fn ($record): string => __(
                                    'job-applications.quick_actions.next_steps.reopened_application',
                                    [],
                                    app()->getLocale(),
                                ))
                                ->required()
                                ->rows(3)
                                ->autosize(),

                            DatePicker::make('next_action_at')
                                ->label(__('job-applications.quick_actions.fields.next_action_at'))
                                ->default(today()->addDays(2)),

                            Textarea::make('notes_to_append')
                                ->label(__('job-applications.quick_actions.fields.notes_to_append'))
                                ->rows(5)
                                ->autosize(),
                        ])
                        ->visible(fn ($record): bool => in_array($record->status, [
                            ApplicationStatus::Paused,
                            ApplicationStatus::Rejected,
                        ], true))
                        ->modalSubmitAction(fn (Action $action): Action => $action->color('primary'))
                        ->action(function ($record, array $data): void {
                            $previousStatus = $record->status;
                            $locale = app()->getLocale();

                            $statusTo = $data['status_to'];
                            $nextStep = trim((string) ($data['next_step'] ?? ''));
                            $notes = trim((string) ($data['notes_to_append'] ?? ''));
                            $nextActionAt = $data['next_action_at'] ?? null;

                            $record->forceFill([
                                'status' => $statusTo,
                                'next_step' => $nextStep,
                                'next_action_at' => $nextActionAt,
                            ])->save();

                            self::createApplicationEvent(
                                $record,
                                JobApplicationEventType::Reopened,
                                __(
                                    'job-application-events.types.reopened',
                                    [],
                                    $locale,
                                ),
                                self::eventBody(null, $notes),
                                $previousStatus,
                                $statusTo,
                                $nextActionAt,
                            );
                        }),

                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function createApplicationEvent(
        JobApplication $record,
        JobApplicationEventType $type,
        string $title,
        ?string $body,
        ApplicationStatus|string|null $statusFrom,
        ApplicationStatus|string|null $statusTo,
        $nextActionAt = null,
    ): void {
        $event = $record->events()->make([
            'type' => $type->value,
            'occurred_at' => now(),
            'title' => $title,
            'body' => $body,
            'status_from' => self::statusValue($statusFrom),
            'status_to' => self::statusValue($statusTo),
            'next_action_at' => $nextActionAt,
        ]);

        $event->saveQuietly();
    }

    private static function eventBody(?string $mainText, ?string $notes = null): ?string
    {
        $mainText = trim((string) $mainText);
        $notes = trim((string) $notes);

        if ($mainText === '') {
            return $notes !== '' ? $notes : null;
        }

        return $notes === ''
            ? $mainText
            : "{$mainText}\n\n{$notes}";
    }

    private static function statusValue(ApplicationStatus|string|null $status): ?string
    {
        if ($status instanceof ApplicationStatus) {
            return $status->value;
        }

        if ($status === null || $status === '') {
            return null;
        }

        return $status;
    }
}
