<?php

namespace App\Filament\Resources\Contacts\Tables;

use App\Filament\Actions\OpenWebmailAction;
use App\Models\Contact;
use App\Services\WebmailProvider;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactsTable
{
    public static function configure(Table $table): Table
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
                    ->action(
                        count($providers) > 1
                            ? OpenWebmailAction::make(
                                fn (Contact $record): ?string => $record->email,
                            )
                            : null,
                    )
                    ->disabledClick(
                        fn (Contact $record): bool => blank($record->email) && blank($record->url),
                    )
                    ->action(
                        OpenWebmailAction::make(
                            fn (Contact $record): ?string => $record->email,
                        ),
                    )
                    ->openUrlInNewTab()
                    ->searchable([
                        'email',
                        'url',
                    ])
                    ->placeholder('-'),

                TextColumn::make('url')
                    ->label(__('contacts.fields.url'))
                    ->placeholder('-')
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->copyable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('contacts.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('contacts.fields.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            ])
            ->defaultSort('updated_at', 'desc')
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
