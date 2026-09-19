<?php

namespace App\Filament\Resources\JobApplications\RelationManagers;

use App\Enums\ApplicationStatus;
use App\Enums\CommunicationChannel;
use App\Enums\InterviewResult;
use App\Enums\JobApplicationEventType;
use App\Filament\Resources\JobApplications\Pages\ViewJobApplication;
use App\Models\Contact;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class EventsRelationManager extends RelationManager
{
    protected static string $relationship = 'events';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('job-application-events.navigation.label');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label(__('job-application-events.fields.type'))
                    ->options(JobApplicationEventType::options())
                    ->default(JobApplicationEventType::ManualNote->value)
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state): void {
                        if (! self::supportsCommunicationChannel($state)) {
                            $set('communication_channel', null);
                        }
                    }),

                DateTimePicker::make('occurred_at')
                    ->label(__('job-application-events.fields.occurred_at'))
                    ->default(now())
                    ->required()
                    ->seconds(false)
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state): void {
                        if (blank($state)) {
                            $set('status_from', null);

                            return;
                        }

                        $occurredAt = Carbon::parse($state);

                        $hasNewerEvent = $this->getOwnerRecord()
                            ->events()
                            ->where('occurred_at', '>', $occurredAt)
                            ->exists();

                        $hasNewerInterview = $this->getOwnerRecord()
                            ->interviews()
                            ->where('interview_at', '>', $occurredAt)
                            ->exists();

                        if (! $hasNewerEvent && ! $hasNewerInterview) {
                            $status = $this->getOwnerRecord()->status;

                            $set(
                                'status_from',
                                $status instanceof ApplicationStatus
                                    ? $status->value
                                    : $status,
                            );

                            return;
                        }

                        $previousEvent = $this->getOwnerRecord()
                            ->events()
                            ->where('occurred_at', '<', $occurredAt)
                            ->whereNotNull('status_to')
                            ->latest('occurred_at')
                            ->first();

                        $previousInterview = $this->getOwnerRecord()
                            ->interviews()
                            ->where('interview_at', '<', $occurredAt)
                            ->where('result', '<>', InterviewResult::Cancelled->value)
                            ->latest('interview_at')
                            ->first();

                        if (
                            $previousInterview !== null
                            && (
                                $previousEvent === null
                                || $previousInterview->interview_at->gt($previousEvent->occurred_at)
                            )
                        ) {
                            $status = $previousInterview->result === InterviewResult::Rejected
                                ? ApplicationStatus::Rejected
                                : ApplicationStatus::Interview;
                        } else {
                            $status = $previousEvent?->status_to;
                        }

                        $set(
                            'status_from',
                            $status instanceof ApplicationStatus
                                ? $status->value
                                : $status,
                        );
                    }),

                TextInput::make('title')
                    ->label(__('job-application-events.fields.title'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(
                        fn (Get $get): int => self::supportsCommunicationChannel($get('type'))
                            ? 1
                            : 2,
                    ),

                Select::make('communication_channel')
                    ->label(__('job-application-events.fields.communication_channel'))
                    ->options(CommunicationChannel::options())
                    ->visible(
                        fn (Get $get): bool => self::supportsCommunicationChannel($get('type')),
                    )
                    ->required(
                        fn (Get $get): bool => in_array(
                            self::eventTypeValue($get('type')),
                            [
                                JobApplicationEventType::CommunicationSent->value,
                                JobApplicationEventType::CommunicationReceived->value,
                            ],
                            true,
                        ),
                    ),

                Select::make('contact_id')
                    ->label(__('contacts.model_label'))
                    ->relationship(
                        name: 'contact',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query
                            ->whereHas(
                                'jobApplications',
                                fn (Builder $query): Builder => $query
                                    ->whereKey($this->getOwnerRecord()->getKey()),
                            ),
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn (Contact $record): string => $record->display_name,
                    )
                    ->searchable([
                        'name',
                        'organization',
                        'email',
                        'url',
                    ])
                    ->preload()
                    ->nullable(),

                DatePicker::make('next_action_at')
                    ->label(__('job-application-events.fields.next_action_at')),

                Select::make('status_from')
                    ->label(__('job-application-events.fields.status_from'))
                    ->options(ApplicationStatus::options())
                    ->default(function (): ?string {
                        $status = $this->getOwnerRecord()->status;

                        return $status instanceof ApplicationStatus
                            ? $status->value
                            : $status;
                    })
                    ->disabled()
                    ->dehydrated(),

                Select::make('status_to')
                    ->label(__('job-application-events.fields.status_to'))
                    ->options(ApplicationStatus::options())
                    ->nullable(),

                Textarea::make('body')
                    ->label(__('job-application-events.fields.body'))
                    ->rows(6)
                    ->autosize()
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel(__('job-application-events.model.label'))
            ->pluralModelLabel(__('job-application-events.model.plural_label'))
            ->heading(__('job-application-events.navigation.label'))
            ->emptyStateHeading(__('job-application-events.empty'))
            ->defaultSort('occurred_at', 'desc')
            ->columns([
                TextColumn::make('occurred_at')
                    ->label(__('job-application-events.fields.occurred_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->wrapHeader(),

                TextColumn::make('type')
                    ->label(__('job-application-events.fields.type'))
                    ->formatStateUsing(fn (JobApplicationEventType|string|null $state): ?string => $state instanceof JobApplicationEventType
                            ? $state->label()
                            : JobApplicationEventType::tryFrom((string) $state)?->label() ?? $state)
                    ->badge()
                    ->sortable(),

                TextColumn::make('communication_channel')
                    ->label(__('job-application-events.fields.communication_channel'))
                    ->formatStateUsing(
                        fn (CommunicationChannel|string|null $state): ?string => $state instanceof CommunicationChannel
                            ? $state->label()
                            : CommunicationChannel::tryFrom((string) $state)?->label(),
                    )
                    ->badge()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('title')
                    ->label(__('job-application-events.fields.title'))
                    ->searchable()
                    ->wrap()
                    ->lineClamp(2),

                TextColumn::make('contact.display_name')
                    ->label(__('contacts.model_label'))
                    ->placeholder('-')
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('body')
                    ->label(__('job-application-events.fields.body'))
                    ->limit(80)
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('status_from')
                    ->label(__('job-application-events.fields.status_from'))
                    ->formatStateUsing(fn (ApplicationStatus|string|null $state): ?string => $state instanceof ApplicationStatus
                            ? $state->label()
                            : ApplicationStatus::tryFrom((string) $state)?->label() ?? $state)
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status_to')
                    ->label(__('job-application-events.fields.status_to'))
                    ->formatStateUsing(fn (ApplicationStatus|string|null $state): ?string => $state instanceof ApplicationStatus
                            ? $state->label()
                            : ApplicationStatus::tryFrom((string) $state)?->label() ?? $state)
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('next_action_at')
                    ->label(__('job-application-events.fields.next_action_at'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable()
                    ->wrapHeader(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('job-application-events.actions.create'))
                    ->after(fn () => $this
                        ->dispatch('job-application-updated')
                        ->to(ViewJobApplication::class)),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->modalHeading(__('job-application-events.actions.view')),
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

    private static function supportsCommunicationChannel(
        JobApplicationEventType|string|null $type,
    ): bool {
        return in_array(
            self::eventTypeValue($type),
            [
                JobApplicationEventType::CommunicationSent->value,
                JobApplicationEventType::CommunicationReceived->value,
                JobApplicationEventType::InquirySent->value,
                JobApplicationEventType::ResponseReceived->value,
                JobApplicationEventType::TechnicalTest->value,
                JobApplicationEventType::FollowUpSent->value,
            ],
            true,
        );
    }

    private static function eventTypeValue(
        JobApplicationEventType|string|null $type,
    ): ?string {
        if ($type instanceof JobApplicationEventType) {
            return $type->value;
        }

        return filled($type) ? $type : null;
    }
}
