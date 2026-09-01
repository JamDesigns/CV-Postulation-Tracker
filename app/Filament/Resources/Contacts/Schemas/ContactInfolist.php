<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('contacts.sections.main'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('contacts.fields.name'))
                            ->placeholder('-'),

                        TextEntry::make('organization')
                            ->label(__('contacts.fields.organization'))
                            ->placeholder('-'),

                        TextEntry::make('url')
                            ->label(__('contacts.fields.url'))
                            ->placeholder('-')
                            ->url(fn (?string $state): ?string => $state)
                            ->openUrlInNewTab()
                            ->copyable(),

                        TextEntry::make('email')
                            ->label(__('contacts.fields.email'))
                            ->placeholder('-')
                            ->copyable(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
