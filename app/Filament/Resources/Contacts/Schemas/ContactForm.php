<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactForm
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
            Section::make(__('contacts.sections.main'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('contacts.fields.name'))
                        ->maxLength(255)
                        ->requiredWithoutAll('organization,url,email')
                        ->validationMessages([
                            'required_without_all' => __('contacts.validation.at_least_one_detail'),
                        ]),

                    TextInput::make('organization')
                        ->label(__('contacts.fields.organization'))
                        ->maxLength(255),

                    TextInput::make('url')
                        ->label(__('contacts.fields.url'))
                        ->url(),

                    TextInput::make('email')
                        ->label(__('contacts.fields.email'))
                        ->email()
                        ->maxLength(255),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }
}
