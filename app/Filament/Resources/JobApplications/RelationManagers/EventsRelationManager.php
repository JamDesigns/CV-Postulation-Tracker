<?php

namespace App\Filament\Resources\JobApplications\RelationManagers;

use App\Enums\ApplicationStatus;
use App\Enums\InterviewResult;
use App\Enums\JobApplicationEventType;
use App\Filament\Resources\JobApplications\Pages\ViewJobApplication;
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
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use LaraZeus\SpatieTranslatable\Resources\RelationManagers\Concerns\Translatable;
use Livewire\Attributes\Reactive;

class EventsRelationManager extends RelationManager
{
    use Translatable;

    /** @disregard P1110 Required to inherit the parent locale reactively. */
    #[Reactive]
    public ?string $activeLocale = null;

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
                    ->required(),

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
                    ->maxLength(255),

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

                TextColumn::make('title')
                    ->label(__('job-application-events.fields.title'))
                    ->searchable()
                    ->wrap()
                    ->lineClamp(2),

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
                    ->modalDescription(
                        fn (): string => __('job-application-events.modal.content_language', [
                            'locale' => ucfirst((string) \Locale::getDisplayLanguage(
                                $this->activeLocale ?? app()->getLocale(),
                                app()->getLocale(),
                            )),
                        ])
                    )
                    ->after(fn () => $this
                        ->dispatch('job-application-updated')
                        ->to(ViewJobApplication::class)),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->modalHeading(__('job-application-events.actions.view'))
                        ->modalDescription(
                            fn (): string => __('job-application-events.modal.content_language', [
                                'locale' => ucfirst((string) \Locale::getDisplayLanguage(
                                    $this->activeLocale ?? app()->getLocale(),
                                    app()->getLocale(),
                                )),
                            ])
                        ),
                    EditAction::make()
                        ->modalDescription(
                            fn (): string => __('job-application-events.modal.content_language', [
                                'locale' => ucfirst((string) \Locale::getDisplayLanguage(
                                    $this->activeLocale ?? app()->getLocale(),
                                    app()->getLocale(),
                                )),
                            ])
                        ),
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
