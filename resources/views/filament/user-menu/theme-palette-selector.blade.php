@php
    $palettes = config('filament-palettes.palettes', []);
    $defaultPalette = config('filament-palettes.default', 'amber');
    $selectedPalette = session('filament_palette', request()->user()?->theme_palette ?? $defaultPalette);
@endphp

<div class="border-t border-gray-200 dark:border-white/10" style="padding: 0.75rem;">
    <div class="mb-2 text-xs font-semibold text-gray-700 dark:text-gray-200">
        {{ __('navigation.user_menu.themes.label') }}
    </div>

    <div style="display: flex; align-items: center; gap: 0.75rem;">
        @foreach ($palettes as $key => $palette)
            @php
                $translationKey = "navigation.user_menu.themes.palettes.{$key}";
                $label = __($translationKey);

                if ($label === $translationKey) {
                    $label = $palette['label'] ?? ucfirst($key);
                }

                $previewColor = $palette['preview'] ?? '#f59e0b';
                $isSelected = $selectedPalette === $key;
            @endphp

            <a href="{{ route('filament.admin.theme-palette.switch', ['palette' => $key]) }}"
                aria-label="{{ $label }}" @if ($isSelected) aria-current="true" @endif
                @class([
                    'inline-block transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900',
                    'text-gray-950 dark:text-white' => $isSelected,
                    'text-transparent' => !$isSelected,
                ])
                style="
                    display: inline-block;
                    width: 1.75rem;
                    height: 1.75rem;
                    border-radius: 9999px;
                    background-color: {{ $previewColor }};
                    border: {{ $isSelected ? '2px solid currentColor' : '1px solid transparent' }};
                    box-shadow: {{ $isSelected ? '0 0 0 1px currentColor' : 'none' }};
                "></a>
        @endforeach
    </div>
</div>
