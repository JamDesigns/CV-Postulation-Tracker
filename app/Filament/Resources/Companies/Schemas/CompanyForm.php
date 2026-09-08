<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::components());
    }

    /**
     * @return array<Component>
     */
    public static function components(): array
    {
        return [
            Section::make(__('companies.model_label'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('companies.fields.name'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->columnSpanFull(),

                    TextInput::make('website')
                        ->label(__('companies.fields.website'))
                        ->url()
                        ->maxLength(255),

                    TextInput::make('linkedin_url')
                        ->label(__('companies.fields.linkedin_url'))
                        ->url()
                        ->maxLength(255),

                    Textarea::make('notes')
                        ->label(__('companies.fields.notes'))
                        ->rows(4)
                        ->autosize()
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }
}
