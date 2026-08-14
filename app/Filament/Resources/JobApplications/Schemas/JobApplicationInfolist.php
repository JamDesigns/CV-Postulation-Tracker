<?php

namespace App\Filament\Resources\JobApplications\Schemas;

use App\Enums\ApplicationStatus;
use App\Enums\Currency;
use App\Enums\SourceType;
use App\Enums\WorkMode;
use App\Models\JobApplication;
use App\Services\ExchangeRateService;
use App\Support\CurrencyFormatter;
use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class JobApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::components());
    }

    public static function components(): array
    {
        return [
            Tabs::make(__('job-applications.sections.details'))
                ->tabs([
                    Tabs\Tab::make(__('job-applications.sections.main'))
                        ->schema([
                            TextEntry::make('company_name')
                                ->label(__('job-applications.fields.company_name')),

                            TextEntry::make('job_title')
                                ->label(__('job-applications.fields.job_title')),

                            TextEntry::make('status')
                                ->label(__('job-applications.fields.status'))
                                ->formatStateUsing(fn (ApplicationStatus|string|null $state): ?string => $state instanceof ApplicationStatus
                                        ? $state->label()
                                        : ApplicationStatus::tryFrom($state ?? '')?->label() ?? $state)
                                ->badge()
                                ->color(fn (ApplicationStatus|string|null $state): string => $state instanceof ApplicationStatus
                                        ? $state->color()
                                        : ApplicationStatus::tryFrom($state ?? '')?->color() ?? 'info'),

                            TextEntry::make('sent_at')
                                ->label(__('job-applications.fields.sent_at'))
                                ->date('d/m/Y')
                                ->placeholder('-'),
                        ])
                        ->columns(2),

                    Tabs\Tab::make(__('job-applications.sections.offer'))
                        ->schema([
                            TextEntry::make('job_url')
                                ->label(__('job-applications.fields.job_url'))
                                ->placeholder('-')
                                ->url(fn (?string $state): ?string => $state)
                                ->openUrlInNewTab()
                                ->copyable()
                                ->columnSpanFull(),

                            TextEntry::make('source')
                                ->label(__('job-applications.fields.source'))
                                ->formatStateUsing(fn (SourceType|string|null $state): ?string => $state instanceof SourceType
                                        ? $state->label()
                                        : SourceType::tryFrom($state ?? '')?->label() ?? $state)
                                ->badge(),

                            TextEntry::make('work_mode')
                                ->label(__('job-applications.fields.work_mode'))
                                ->formatStateUsing(fn (WorkMode|string|null $state): ?string => $state instanceof WorkMode
                                        ? $state->label()
                                        : WorkMode::tryFrom($state ?? '')?->label() ?? $state)
                                ->badge(),

                            TextEntry::make('location')
                                ->label(__('job-applications.fields.location'))
                                ->placeholder('-'),

                            TextEntry::make('salary')
                                ->label(__('job-applications.fields.salary'))
                                ->state(fn (JobApplication $record): ?string => self::formattedSalary($record))
                                ->placeholder('-'),

                            TextEntry::make('salary_conversion')
                                ->label(__('job-applications.fields.salary_conversion'))
                                ->state(fn (JobApplication $record): string => self::salaryConversion($record))
                                ->visible(
                                    fn (JobApplication $record): bool => self::shouldShowSalaryConversion($record),
                                )
                                ->color('gray'),

                            TextEntry::make('main_stack')
                                ->label(__('job-applications.fields.main_stack'))
                                ->placeholder('-')
                                ->columnSpanFull(),
                        ])
                        ->columns(3),

                    Tabs\Tab::make(__('job-applications.sections.candidate_materials'))
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextEntry::make('cvVersion.name')
                                        ->label(__('job-applications.fields.cv_version_id'))
                                        ->placeholder('-'),

                                    TextEntry::make('cv_pdf_file')
                                        ->label(__('cv-versions.fields.pdf_path'))
                                        ->placeholder('-')
                                        ->state(fn ($record): ?string => $record->cvVersion?->pdf_path ? $record->cvVersion->pdfFriendlyName() : null)
                                        ->prefixAction(
                                            Action::make('openCvPdf')
                                                ->label(__('cv-versions.actions.open_pdf'))
                                                ->icon(Heroicon::ArrowTopRightOnSquare)
                                                ->iconButton()
                                                ->tooltip(__('cv-versions.actions.open_pdf'))
                                                ->color('primary')
                                                ->url(fn ($record): ?string => $record->cvVersion?->pdf_path ? route('filament.admin.cv-versions.pdf', $record->cvVersion) : null)
                                                ->openUrlInNewTab()
                                                ->visible(fn ($record): bool => filled($record->cvVersion?->pdf_path)),
                                        )
                                        ->limit(60),

                                    TextEntry::make('cv_docx_file')
                                        ->label(__('cv-versions.fields.docx_path'))
                                        ->placeholder('-')
                                        ->state(fn ($record): ?string => $record->cvVersion?->docx_path ? $record->cvVersion->docxFriendlyName() : null)
                                        ->prefixAction(
                                            Action::make('downloadCvDocx')
                                                ->label(__('cv-versions.actions.download_docx'))
                                                ->icon(Heroicon::ArrowDownTray)
                                                ->iconButton()
                                                ->tooltip(__('cv-versions.actions.download_docx'))
                                                ->color('primary')
                                                ->url(fn ($record): ?string => $record->cvVersion?->docx_path ? route('filament.admin.cv-versions.docx', $record->cvVersion) : null)
                                                ->visible(fn ($record): bool => filled($record->cvVersion?->docx_path)),
                                        )
                                        ->limit(60),
                                ]),

                            Grid::make(4)
                                ->schema([
                                    IconEntry::make('dossier_sent')
                                        ->label(__('job-applications.fields.dossier_sent'))
                                        ->boolean()
                                        ->state(false)
                                        ->visible(fn ($record): bool => ! ((bool) $record->dossier_sent && filled($record->technical_dossier_version_id))),

                                    TextEntry::make('technicalDossierVersion.name')
                                        ->label(__('job-applications.fields.technical_dossier_version_id'))
                                        ->placeholder('-')
                                        ->formatStateUsing(fn ($record): ?string => $record->technicalDossierVersion
                                                ? "{$record->technicalDossierVersion->name} {$record->technicalDossierVersion->version_label}"
                                                : null)
                                        ->visible(fn ($record): bool => (bool) $record->dossier_sent && filled($record->technical_dossier_version_id)),

                                    TextEntry::make('technical_dossier_pdf_file')
                                        ->label(__('technical-dossier-versions.fields.pdf_path'))
                                        ->placeholder('-')
                                        ->state(fn ($record): ?string => $record->technicalDossierVersion?->pdf_path
                                                ? $record->technicalDossierVersion->pdfFriendlyName()
                                                : null)
                                        ->prefixAction(
                                            Action::make('openTechnicalDossierPdf')
                                                ->label(__('technical-dossier-versions.actions.open_pdf'))
                                                ->icon(Heroicon::ArrowTopRightOnSquare)
                                                ->iconButton()
                                                ->tooltip(__('technical-dossier-versions.actions.open_pdf'))
                                                ->color('primary')
                                                ->url(fn ($record): ?string => $record->technicalDossierVersion?->pdf_path
                                                        ? route('filament.admin.technical-dossier-versions.pdf', [
                                                            'technicalDossierVersion' => $record->technicalDossierVersion,
                                                        ])
                                                        : null)
                                                ->openUrlInNewTab()
                                                ->visible(fn ($record): bool => filled($record->technicalDossierVersion?->pdf_path)),
                                        )
                                        ->visible(fn ($record): bool => (bool) $record->dossier_sent && filled($record->technical_dossier_version_id))
                                        ->limit(60),

                                    TextEntry::make('technical_dossier_docx_file')
                                        ->label(__('technical-dossier-versions.fields.docx_path'))
                                        ->placeholder('-')
                                        ->state(fn ($record): ?string => $record->technicalDossierVersion?->docx_path
                                                ? $record->technicalDossierVersion->docxFriendlyName()
                                                : null)
                                        ->prefixAction(
                                            Action::make('downloadTechnicalDossierDocx')
                                                ->label(__('technical-dossier-versions.actions.download_docx'))
                                                ->icon(Heroicon::ArrowDownTray)
                                                ->iconButton()
                                                ->tooltip(__('technical-dossier-versions.actions.download_docx'))
                                                ->color('primary')
                                                ->url(fn ($record): ?string => $record->technicalDossierVersion?->docx_path
                                                        ? route('filament.admin.technical-dossier-versions.docx', [
                                                            'technicalDossierVersion' => $record->technicalDossierVersion,
                                                        ])
                                                        : null)
                                                ->visible(fn ($record): bool => filled($record->technicalDossierVersion?->docx_path)),
                                        )
                                        ->visible(fn ($record): bool => (bool) $record->dossier_sent && filled($record->technical_dossier_version_id))
                                        ->limit(60),
                                ]),

                            TextEntry::make('adaptation_summary')
                                ->label(__('job-applications.fields.adaptation_summary'))
                                ->placeholder('-')
                                ->columnSpanFull(),
                        ]),

                    Tabs\Tab::make(__('job-applications.sections.follow_up'))
                        ->schema([
                            TextEntry::make('recruiter_name')
                                ->label(__('job-applications.fields.recruiter_name'))
                                ->placeholder('-'),

                            TextEntry::make('recruiter_url')
                                ->label(__('job-applications.fields.recruiter_url'))
                                ->placeholder('-')
                                ->url(fn (?string $state): ?string => $state)
                                ->openUrlInNewTab()
                                ->copyable(),

                            TextEntry::make('recruiter_email')
                                ->label(__('job-applications.fields.recruiter_email'))
                                ->placeholder('-')
                                ->copyable(),

                            TextEntry::make('next_step')
                                ->label(__('job-applications.fields.next_step'))
                                ->placeholder('-')
                                ->columnSpanFull(),

                            TextEntry::make('next_action_at')
                                ->label(__('job-applications.fields.next_action_at'))
                                ->date('d/m/Y')
                                ->placeholder('-'),
                        ])
                        ->columns(2),

                    Tabs\Tab::make(__('job-applications.sections.metadata'))
                        ->schema([
                            Section::make()
                                ->schema([
                                    TextEntry::make('created_at')
                                        ->label(__('job-applications.fields.created_at'))
                                        ->dateTime('d/m/Y H:i')
                                        ->placeholder('-'),

                                    TextEntry::make('updated_at')
                                        ->label(__('job-applications.fields.updated_at'))
                                        ->dateTime('d/m/Y H:i')
                                        ->placeholder('-'),
                                ])
                                ->columns(2),
                        ]),
                ])
                ->columnSpanFull(),
        ];
    }

    private static function currencyFor(JobApplication $jobApplication): ?Currency
    {
        return $jobApplication->currency instanceof Currency
            ? $jobApplication->currency
            : Currency::tryFrom((string) $jobApplication->currency);
    }

    private static function formattedSalary(JobApplication $jobApplication): ?string
    {
        $currency = self::currencyFor($jobApplication);

        if ($jobApplication->salary === null || $currency === null) {
            return null;
        }

        return CurrencyFormatter::format(
            $jobApplication->salary,
            $currency,
        );
    }

    private static function shouldShowSalaryConversion(JobApplication $jobApplication): bool
    {
        $currency = self::currencyFor($jobApplication);

        return $jobApplication->salary !== null
            && $currency !== null
            && $currency !== Currency::localForLocale();
    }

    private static function salaryConversion(JobApplication $jobApplication): string
    {
        $sourceCurrency = self::currencyFor($jobApplication);

        if ($sourceCurrency === null) {
            return '';
        }

        $targetCurrency = Currency::localForLocale();
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
                    (float) $jobApplication->salary * $rate,
                    $targetCurrency,
                ),
            ],
        );
    }
}
