<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RegisterFilamentThemePalette
{
    public function handle(Request $request, Closure $next): Response
    {
        FilamentColor::register(function () use ($request): array {
            $palettes = config('filament-palettes.palettes', []);
            $defaultPalette = config('filament-palettes.default', 'amber');

            $selectedPalette = session(
                'filament_palette',
                $request->user()?->theme_palette ?? $defaultPalette,
            );

            return $palettes[$selectedPalette]['colors']
                ?? $palettes[$defaultPalette]['colors']
                ?? [];
        });

        return $next($request);
    }
}
