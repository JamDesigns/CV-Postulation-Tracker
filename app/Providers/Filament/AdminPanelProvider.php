<?php

namespace App\Providers\Filament;

use App\Http\Controllers\AttachmentFileController;
use App\Http\Controllers\CvVersionFileController;
use App\Http\Controllers\TechnicalDossierVersionFileController;
use App\Http\Middleware\RegisterFilamentThemePalette;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandLogo(asset('images/branding/logo.png'))
            ->darkModeBrandLogo(asset('images/branding/logo-dark.png'))
            ->brandLogoHeight('4rem')
            ->favicon(asset('favicon.ico'))
            ->plugins([
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->databaseNotifications()
            ->authenticatedRoutes(function (): void {
                Route::get('/cv-versions/{cvVersion}/pdf', [CvVersionFileController::class, 'showPdf'])
                    ->name('cv-versions.pdf');

                Route::get('/cv-versions/{cvVersion}/docx', [CvVersionFileController::class, 'downloadDocx'])
                    ->name('cv-versions.docx');

                Route::get('/technical-dossier-versions/{technicalDossierVersion}/pdf', [TechnicalDossierVersionFileController::class, 'showPdf'])
                    ->name('technical-dossier-versions.pdf');

                Route::get('/technical-dossier-versions/{technicalDossierVersion}/docx', [TechnicalDossierVersionFileController::class, 'downloadDocx'])
                    ->name('technical-dossier-versions.docx');

                Route::get('/attachments/{attachment}/file', [AttachmentFileController::class, 'show'])
                    ->name('attachments.file');

                Route::get('/attachments/{attachment}/download', [AttachmentFileController::class, 'download'])
                    ->name('attachments.download');

                Route::get('/theme-palette/{palette}', function (string $palette) {
                    abort_unless(
                        array_key_exists($palette, config('filament-palettes.palettes', [])),
                        404,
                    );

                    request()->user()?->forceFill([
                        'theme_palette' => $palette,
                    ])->save();

                    session(['filament_palette' => $palette]);

                    return redirect()->back();
                })->name('theme-palette.switch');
            })
            ->userMenuItems([
                Action::make('themePaletteSelector')
                    ->view('filament.user-menu.theme-palette-selector'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // AccountWidget::class,
                // FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                RegisterFilamentThemePalette::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
