<x-filament-panels::page>
    <div style="display: flex; justify-content: flex-end;">
        <div style="width: 100%; max-width: 12rem;">
            <label for="period" class="mb-2 block text-sm font-medium text-gray-950 dark:text-white">
                {{ __('job-search-statistics.period.label') }}
            </label>

            <x-filament::input.wrapper>
                <x-filament::input.select id="period" wire:model.live="period">
                    @foreach ($this->periodOptions() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>
    </div>

    @php
    $statistics = $this->statistics();
    @endphp

    <div style="
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    ">
        @foreach ([
        'sent',
        'responses',
        'interviews',
        'technical_tests',
        'rejected',
        'hired',
        ] as $stat)
        <x-filament::section>
            <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __("job-search-statistics.stats.{$stat}") }}
                </span>

                <span class="text-3xl font-semibold text-gray-950 dark:text-white">
                    {{ $statistics[$stat] }}
                </span>
            </div>
        </x-filament::section>
        @endforeach
    </div>

    <div style="
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    ">
        @foreach ([
        'response' => $this->percentage($statistics['responses'], $statistics['sent']),
        'interview' => $this->percentage($statistics['interviews'], $statistics['sent']),
        'rejection' => $this->percentage($statistics['rejected'], $statistics['sent']),
        ] as $rate => $value)
        <x-filament::section>
            <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __("job-search-statistics.rates.{$rate}") }}
                </span>

                <span class="text-3xl font-semibold text-gray-950 dark:text-white">
                    {{ \Illuminate\Support\Number::percentage(
                        $value,
                        precision: 1,
                        locale: app()->getLocale(),
                    ) }}
                </span>
            </div>
        </x-filament::section>
        @endforeach
    </div>

    <div style="
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        align-items: start;
    ">
        @livewire(
        \App\Filament\Widgets\JobSearchApplicationsEvolutionChart::class,
        [
        'periodStart' => $this->periodStart()?->toDateString(),
        ],
        key('job-search-applications-evolution-'.$this->period),
        )

        @livewire(
        \App\Filament\Widgets\JobSearchApplicationsByStatusChart::class,
        [
        'periodStart' => $this->periodStart()?->toDateString(),
        ],
        key('job-search-applications-by-status-'.$this->period),
        )
    </div>

    <div style="
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        align-items: start;
    ">
        @livewire(
        \App\Filament\Widgets\JobSearchApplicationsByWorkModeChart::class,
        [
        'periodStart' => $this->periodStart()?->toDateString(),
        ],
        key('job-search-applications-by-work-mode-'.$this->period),
        )

        @livewire(
        \App\Filament\Widgets\JobSearchApplicationsBySourceChart::class,
        [
        'periodStart' => $this->periodStart()?->toDateString(),
        ],
        key('job-search-applications-by-source-'.$this->period),
        )
    </div>

    <div style="
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        align-items: start;
    ">
        @livewire(
        \App\Filament\Widgets\JobSearchRejectionReasonsChart::class,
        [
        'periodStart' => $this->periodStart()?->toDateString(),
        ],
        key('job-search-rejection-reasons-'.$this->period),
        )

        @livewire(
        \App\Filament\Widgets\JobSearchFunnelChart::class,
        [
        'periodStart' => $this->periodStart()?->toDateString(),
        ],
        key('job-search-funnel-'.$this->period),
        )
    </div>
</x-filament-panels::page>
