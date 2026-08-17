<?php

namespace App\Providers;

use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Illuminate\Support\ServiceProvider;
use Spatie\Translatable\Facades\Translatable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Translatable::allowNullForTranslation();
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch): void {
            $switch
                ->locales(config('locales'))
                ->flags([
                    'es' => asset('flags/es.svg'),
                    'en' => asset('flags/gb.svg'),
                    'fr' => asset('flags/fr.svg'),
                ])
                ->nativeLabel();
        });
    }
}
