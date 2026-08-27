<?php

namespace App\Filament\Resources\JobApplications\Schemas;

use App\Enums\ApplicationStatus;
use App\Enums\BaseProfile;
use App\Enums\Currency;
use App\Enums\CvLanguage;
use App\Enums\SourceType;
use App\Enums\WorkMode;
use App\Models\CvVersion;
use App\Models\JobApplication;
use App\Models\TechnicalDossierVersion;
use App\Services\ExchangeRateService;
use App\Support\CurrencyFormatter;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class JobApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make(__('job-applications.sections.form'))
                    ->tabs([
                        Tabs\Tab::make(__('job-applications.sections.main'))
                            ->schema([
                                TextInput::make('company_name')
                                    ->label(__('job-applications.fields.company_name'))
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('job_title')
                                    ->label(__('job-applications.fields.job_title'))
                                    ->required()
                                    ->maxLength(255)
                                    ->rule(fn (Get $get, $record) => Rule::unique('job_applications', 'job_title')
                                        ->where(fn (Builder $query): Builder => $query
                                            ->where('company_name', $get('company_name'))
                                            ->when(
                                                filled($get('job_url')),
                                                fn (Builder $query): Builder => $query->where('job_url', $get('job_url')),
                                                fn (Builder $query): Builder => $query->whereNull('job_url'),
                                            ))
                                        ->ignore($record))
                                    ->validationMessages([
                                        'unique' => __('job-applications.validation.duplicate'),
                                    ]),

                                Select::make('status')
                                    ->label(__('job-applications.fields.status'))
                                    ->options(ApplicationStatus::options())
                                    ->default(ApplicationStatus::Pending->value)
                                    ->required()
                                    ->live(),

                                DatePicker::make('sent_at')
                                    ->label(__('job-applications.fields.sent_at'))
                                    ->live(),
                            ])
                            ->columns(2),

                        Tabs\Tab::make(__('job-applications.sections.offer'))
                            ->schema([
                                TextInput::make('job_url')
                                    ->label(__('job-applications.fields.job_url'))
                                    ->url()
                                    ->columnSpanFull(),

                                Select::make('source')
                                    ->label(__('job-applications.fields.source'))
                                    ->options(SourceType::options())
                                    ->default(SourceType::Linkedin->value)
                                    ->required(),

                                Select::make('work_mode')
                                    ->label(__('job-applications.fields.work_mode'))
                                    ->options(WorkMode::options())
                                    ->default(WorkMode::Remote->value)
                                    ->required(),

                                TextInput::make('location')
                                    ->label(__('job-applications.fields.location'))
                                    ->maxLength(255),

                                TextInput::make('salary')
                                    ->label(__('job-applications.fields.salary'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->live(),

                                Select::make('currency')
                                    ->label(__('job-applications.fields.currency'))
                                    ->options(Currency::options())
                                    ->default(fn (): string => Currency::localForLocale()->value)
                                    ->live(),

                                Text::make(
                                    fn (Get $get, ?JobApplication $record): string => self::salaryConversion(
                                        $get,
                                        $record,
                                    ),
                                )
                                    ->visible(fn (Get $get): bool => self::shouldShowSalaryConversion($get))
                                    ->color('gray')
                                    ->extraAttributes([
                                        'class' => 'block pt-0 lg:pt-8 text-sm',
                                    ]),

                                Textarea::make('main_stack')
                                    ->label(__('job-applications.fields.main_stack'))
                                    ->rows(3)
                                    ->extraInputAttributes(['style' => 'min-height: 5.5rem; resize: vertical;'])
                                    ->columnSpanFull(),
                            ])
                            ->columns(3),

                        Tabs\Tab::make(__('job-applications.sections.candidate_materials'))
                            ->schema([
                                Select::make('cv_version_id')
                                    ->label(__('job-applications.fields.cv_version_id'))
                                    ->relationship('cvVersion', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Tabs::make(__('cv-versions.sections.form'))
                                            ->tabs([
                                                Tabs\Tab::make(__('cv-versions.sections.main'))
                                                    ->schema([
                                                        TextInput::make('name')
                                                            ->label(__('cv-versions.fields.name'))
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->rule(Rule::unique('cv_versions', 'name'))
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
                                    ])
                                    ->createOptionUsing(function (Select $component, array $data, Schema $schema) {
                                        $record = $component->getRelationship()->newModelInstance();

                                        $record->fill($data);
                                        $record->save();

                                        $schema->model($record)->saveRelationships();

                                        return $record->getKey();
                                    })
                                    ->createOptionAction(fn (Action $action): Action => $action
                                        ->label(__('cv-versions.actions.create'))
                                        ->modalHeading(__('cv-versions.actions.create')))
                                    ->required(fn (Get $get): bool => filled($get('sent_at')) || in_array(
                                        ApplicationStatus::tryFrom((string) $get('status')),
                                        [
                                            ApplicationStatus::Sent,
                                            ApplicationStatus::Responded,
                                            ApplicationStatus::Interview,
                                            ApplicationStatus::TechnicalTest,
                                            ApplicationStatus::FollowUpSent,
                                            ApplicationStatus::Hired,
                                        ],
                                        true,
                                    ))
                                    ->live(),

                                Grid::make(1)
                                    ->schema([
                                        Toggle::make('dossier_sent')
                                            ->label(__('job-applications.fields.dossier_sent'))
                                            ->default(false)
                                            ->live()
                                            ->disabled(fn (Get $get): bool => $get('cv_version_id') === null || $get('cv_version_id') === ''),

                                        Text::make(fn (Get $get, $record = null): string => self::technicalDossierVersionPreviewLabel(
                                            $get('cv_version_id'),
                                            $record,
                                        ))
                                            ->color('gray')
                                            ->visible(fn (Get $get): bool => (bool) $get('dossier_sent')),
                                    ])
                                    ->columnSpan(1),

                                Textarea::make('adaptation_summary')
                                    ->label(__('job-applications.fields.adaptation_summary'))
                                    ->rows(3)
                                    ->extraInputAttributes(['style' => 'min-height: 5.5rem; resize: vertical;'])
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tabs\Tab::make(__('job-applications.sections.follow_up'))
                            ->schema([
                                TextInput::make('recruiter_name')
                                    ->label(__('job-applications.fields.recruiter_name'))
                                    ->maxLength(255),

                                TextInput::make('recruiter_url')
                                    ->label(__('job-applications.fields.recruiter_url'))
                                    ->url(),

                                TextInput::make('recruiter_email')
                                    ->label(__('job-applications.fields.recruiter_email'))
                                    ->email()
                                    ->maxLength(255),

                                DatePicker::make('next_action_at')
                                    ->label(__('job-applications.fields.next_action_at')),

                                Textarea::make('next_step')
                                    ->label(__('job-applications.fields.next_step'))
                                    ->rows(3)
                                    ->extraInputAttributes(['style' => 'min-height: 5.5rem; resize: vertical;'])
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function technicalDossierVersionPreviewLabel(int|string|null $cvVersionId, $record = null): string
    {
        if (
            $record?->technicalDossierVersion
            && (string) $record->cv_version_id === (string) $cvVersionId
        ) {
            return self::technicalDossierVersionLabel($record->technicalDossierVersion);
        }

        return self::activeTechnicalDossierVersionLabelForCv($cvVersionId) ?? __('job-applications.fields.no_active_dossier_for_cv_language');
    }

    private static function activeTechnicalDossierVersionLabelForCv(int|string|null $cvVersionId): ?string
    {
        if ($cvVersionId === null || $cvVersionId === '') {
            return null;
        }

        $cvVersion = CvVersion::query()
            ->whereKey($cvVersionId)
            ->first();

        if (! $cvVersion) {
            return null;
        }

        $language = $cvVersion->language instanceof CvLanguage
            ? $cvVersion->language->value
            : (string) $cvVersion->language;

        $technicalDossierVersion = TechnicalDossierVersion::query()
            ->where('language', '=', $language, 'and')
            ->where('is_active', '=', true, 'and')
            ->orderBy('published_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (! $technicalDossierVersion) {
            return null;
        }

        return self::technicalDossierVersionLabel($technicalDossierVersion);
    }

    private static function technicalDossierVersionLabel(TechnicalDossierVersion $technicalDossierVersion): string
    {
        return "{$technicalDossierVersion->name} {$technicalDossierVersion->version_label}";
    }

    private static function shouldShowSalaryConversion(Get $get): bool
    {
        $currency = Currency::tryFrom((string) $get('currency'));

        return filled($get('salary'))
            && $currency !== null
            && $currency !== Currency::localForLocale();
    }

    private static function salaryConversion(
        Get $get,
        ?JobApplication $jobApplication,
    ): string {
        $sourceCurrency = Currency::tryFrom((string) $get('currency'));
        $targetCurrency = Currency::localForLocale();

        if ($sourceCurrency === null) {
            return '';
        }

        $rate = app(ExchangeRateService::class)->rateFor(
            $jobApplication,
            $sourceCurrency,
            $targetCurrency,
        );

        if ($rate === null) {
            return __('job-applications.salary_conversion.rate_unavailable');
        }

        return __(
            'job-applications.salary_conversion.equivalent',
            [
                'amount' => CurrencyFormatter::format(
                    (float) $get('salary') * $rate,
                    $targetCurrency,
                ),
            ],
        );
    }
}
