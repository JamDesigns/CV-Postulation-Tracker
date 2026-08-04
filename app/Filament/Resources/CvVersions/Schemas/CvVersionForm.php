<?php

namespace App\Filament\Resources\CvVersions\Schemas;

use App\Enums\BaseProfile;
use App\Enums\CvLanguage;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class CvVersionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make(__('cv-versions.sections.form'))
                    ->tabs([
                        Tabs\Tab::make(__('cv-versions.sections.main'))
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('cv-versions.fields.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => __('cv-versions.validation.name_unique'),
                                    ])
                                    ->columnSpanFull(),

                                Select::make('language')
                                    ->label(__('cv-versions.fields.language'))
                                    ->options(CvLanguage::options())
                                    ->required()
                                    ->default(CvLanguage::Spanish->value),

                                Select::make('base_profile')
                                    ->label(__('cv-versions.fields.base_profile'))
                                    ->options(BaseProfile::options())
                                    ->required()
                                    ->default(BaseProfile::FullStack->value),
                            ])
                            ->columns(2),

                        Tabs\Tab::make(__('cv-versions.sections.files'))
                            ->schema([
                                FileUpload::make('pdf_path')
                                    ->label(__('cv-versions.fields.pdf_path'))
                                    ->extraAttributes(['class' => 'compact-file-upload'])
                                    ->disk('local')
                                    ->directory('cv-versions/pdf')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->getUploadedFileUsing(function (FileUpload $component, string $file, string|array|null $storedFileNames): ?array {
                                        $uploadedFile = $component->getUploadedFile($file, $storedFileNames);

                                        if (! $uploadedFile) {
                                            return null;
                                        }

                                        $record = $component->getRecord();

                                        if ($record?->pdf_path) {
                                            $uploadedFile['name'] = $record->pdfFriendlyName();
                                        }

                                        return $uploadedFile;
                                    })
                                    ->downloadable()
                                    ->openable(),

                                FileUpload::make('docx_path')
                                    ->label(__('cv-versions.fields.docx_path'))
                                    ->extraAttributes(['class' => 'compact-file-upload'])
                                    ->disk('local')
                                    ->directory('cv-versions/docx')
                                    ->acceptedFileTypes([
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/zip',
                                        'application/x-zip',
                                        'application/x-zip-compressed',
                                        'application/octet-stream',
                                        '.docx',
                                    ])
                                    ->mimeTypeMap([
                                        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    ])
                                    ->rules(['extensions:docx'])
                                    ->getUploadedFileUsing(function (FileUpload $component, string $file, string|array|null $storedFileNames): ?array {
                                        $uploadedFile = $component->getUploadedFile($file, $storedFileNames);

                                        if (! $uploadedFile) {
                                            return null;
                                        }

                                        $record = $component->getRecord();

                                        if ($record?->docx_path) {
                                            $uploadedFile['name'] = $record->docxFriendlyName();
                                        }

                                        return $uploadedFile;
                                    })
                                    ->downloadable(),
                            ])
                            ->columns(2),

                        Tabs\Tab::make(__('cv-versions.sections.adaptation'))
                            ->schema([
                                Textarea::make('highlighted_stack')
                                    ->label(__('cv-versions.fields.highlighted_stack'))
                                    ->rows(3)
                                    ->extraInputAttributes(['style' => 'min-height: 5.5rem; resize: vertical;']),

                                Textarea::make('highlighted_experience')
                                    ->label(__('cv-versions.fields.highlighted_experience'))
                                    ->rows(3)
                                    ->extraInputAttributes(['style' => 'min-height: 5.5rem; resize: vertical;']),

                                Textarea::make('adaptation_notes')
                                    ->label(__('cv-versions.fields.adaptation_notes'))
                                    ->rows(3)
                                    ->extraInputAttributes(['style' => 'min-height: 5.5rem; resize: vertical;'])
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
