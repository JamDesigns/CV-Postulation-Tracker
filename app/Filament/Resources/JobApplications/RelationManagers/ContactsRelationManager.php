<?php

namespace App\Filament\Resources\JobApplications\RelationManagers;

use App\Enums\ApplicationContactRole;
use App\Enums\ApplicationContactSource;
use App\Filament\Actions\OpenWebmailAction;
use App\Filament\Resources\Contacts\Schemas\ContactForm;
use App\Filament\Resources\Contacts\Schemas\ContactInfolist;
use App\Models\Contact;
use App\Models\JobApplicationContact;
use App\Services\WebmailProvider;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('contacts.plural_model_label');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function infolist(Schema $schema): Schema
    {
        return ContactInfolist::configure($schema);
    }

    public function table(Table $table): Table
    {
        $webmail = app(WebmailProvider::class);
        $providers = $webmail->options();

        $singleProvider = count($providers) === 1
            ? (string) array_key_first($providers)
            : null;

        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('contacts.fields.name'))
                    ->searchable([
                        'name',
                        'organization',
                        'email',
                        'url',
                    ])
                    ->wrap()
                    ->placeholder('-'),

                TextColumn::make('organization')
                    ->label(__('contacts.fields.organization'))
                    ->placeholder('-')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('contact_channel')
                    ->label(__('contacts.fields.contact_channel'))
                    ->state(function (Contact $record): ?string {
                        if (filled($record->email)) {
                            return $record->email;
                        }

                        if (filled($record->url)) {
                            return str_contains(strtolower($record->url), 'linkedin.com')
                                ? 'LinkedIn'
                                : $record->url;
                        }

                        return null;
                    })
                    ->url(function (Contact $record) use ($webmail, $singleProvider): ?string {
                        if (blank($record->email)) {
                            return $record->url;
                        }

                        if ($singleProvider === null) {
                            return null;
                        }

                        return $webmail->composeUrl(
                            $singleProvider,
                            $record->email,
                        );
                    })
                    ->openUrlInNewTab()
                    ->disabledClick(
                        fn (Contact $record): bool => blank($record->email) && blank($record->url),
                    )
                    ->action(
                        OpenWebmailAction::make(
                            fn (Contact $record): ?string => $record->email,
                        ),
                    )
                    ->searchable([
                        'email',
                        'url',
                    ])
                    ->placeholder('-'),

                TextColumn::make('role')
                    ->label(__('contacts.fields.role'))
                    ->state(fn (Contact $record): ?string => $record->pivot?->role?->label())
                    ->placeholder('-'),

                IconColumn::make('is_primary')
                    ->label(__('contacts.fields.is_primary'))
                    ->state(fn (Contact $record): bool => (bool) $record->pivot?->is_primary)
                    ->alignCenter()
                    ->boolean(),

                TextColumn::make('source')
                    ->label(__('contacts.fields.source'))
                    ->state(fn (Contact $record): ?string => $record->pivot?->source?->label())
                    ->placeholder('-'),
            ])
            ->recordTitle(fn (Contact $record): string => $record->display_name)
            ->headerActions([
                CreateAction::make()
                    ->label(__('contacts.actions.create'))
                    ->modalHeading(__('contacts.actions.create'))
                    ->schema([
                        ...ContactForm::components(),

                        Section::make(__('contacts.sections.relationship'))
                            ->schema([
                                Select::make('role')
                                    ->label(__('contacts.fields.role'))
                                    ->options(ApplicationContactRole::options()),

                                Select::make('source')
                                    ->label(__('contacts.fields.source'))
                                    ->options(ApplicationContactSource::options()),

                                Toggle::make('is_primary')
                                    ->label(__('contacts.fields.is_primary'))
                                    ->inline(false)
                                    ->default(false),

                                Textarea::make('context')
                                    ->label(__('contacts.fields.context'))
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),
                AttachAction::make()
                    ->label(__('contacts.actions.attach_existing'))
                    ->modalHeading(__('contacts.actions.attach_existing'))
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel(__('contacts.actions.attach'))
                    ->extraModalFooterActions(
                        fn (AttachAction $action): array => $action->canAttachAnother()
                            ? [
                                $action->makeModalSubmitAction('attachAnother', ['another' => true])
                                    ->label(__('contacts.actions.attach_another')),
                            ]
                            : [],
                    )
                    ->preloadRecordSelect()
                    ->recordSelect(
                        fn (Select $select): Select => $select
                            ->label(__('contacts.model_label'))
                            ->hiddenLabel(false),
                    )
                    ->recordSelectSearchColumns([
                        'name',
                        'organization',
                        'email',
                        'url',
                    ])
                    ->schema(fn (AttachAction $action): array => [
                        Grid::make(3)
                            ->schema([
                                $action->getRecordSelect()
                                    ->columnSpanFull(),

                                Select::make('role')
                                    ->label(__('contacts.fields.role'))
                                    ->options(ApplicationContactRole::options()),

                                Select::make('source')
                                    ->label(__('contacts.fields.source'))
                                    ->options(ApplicationContactSource::options()),

                                Toggle::make('is_primary')
                                    ->label(__('contacts.fields.is_primary'))
                                    ->inline(false)
                                    ->default(false),

                                Textarea::make('context')
                                    ->label(__('contacts.fields.context'))
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    Action::make('editRelationship')
                        ->label(__('contacts.actions.edit_relationship'))
                        ->modalHeading(__('contacts.actions.edit_relationship'))
                        ->modalWidth('2xl')
                        ->modalSubmitActionLabel(__('contacts.actions.save'))
                        ->color('primary')
                        ->fillForm(fn (Contact $record): array => [
                            'role' => $record->pivot?->role?->value,
                            'is_primary' => (bool) $record->pivot?->is_primary,
                            'source' => $record->pivot?->source?->value,
                            'context' => $record->pivot?->context,
                        ])
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    Select::make('role')
                                        ->label(__('contacts.fields.role'))
                                        ->options(ApplicationContactRole::options()),

                                    Select::make('source')
                                        ->label(__('contacts.fields.source'))
                                        ->options(ApplicationContactSource::options()),

                                    Toggle::make('is_primary')
                                        ->label(__('contacts.fields.is_primary'))
                                        ->inline(false),

                                    Textarea::make('context')
                                        ->label(__('contacts.fields.context'))
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ]),
                        ])
                        ->action(function (Contact $record, array $data): void {
                            $pivot = $record->pivot;

                            if (! $pivot instanceof JobApplicationContact) {
                                return;
                            }

                            $pivot->fill($data);
                            $pivot->save();
                        }),

                    DetachAction::make(),
                ]),
            ]);
    }
}
