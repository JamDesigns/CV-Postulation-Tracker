<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('companies.model_label'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('companies.fields.name')),

                        TextEntry::make('website')
                            ->label(__('companies.fields.website'))
                            ->placeholder('-')
                            ->url(fn (?string $state): ?string => $state)
                            ->openUrlInNewTab()
                            ->copyable(),

                        TextEntry::make('linkedin_url')
                            ->label(__('companies.fields.linkedin_url'))
                            ->placeholder('-')
                            ->url(fn (?string $state): ?string => $state)
                            ->openUrlInNewTab()
                            ->copyable(),

                        TextEntry::make('notes')
                            ->label(__('companies.fields.notes'))
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
