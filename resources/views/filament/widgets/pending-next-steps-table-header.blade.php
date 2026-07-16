<div class="px-4 py-4 sm:px-6">
    <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between">
        <div class="text-base font-semibold text-gray-950 dark:text-white">
            {{ __('dashboard.widgets.pending_next_steps.heading') }}
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ __('dashboard.widgets.pending_next_steps.urgency') }}:
            </span>

            <x-filament::badge color="danger">
                {{ \App\Enums\NextActionUrgency::Overdue->label() }}
            </x-filament::badge>

            <x-filament::badge color="primary">
                {{ \App\Enums\NextActionUrgency::Today->label() }}
            </x-filament::badge>

            <x-filament::badge color="info">
                {{ \App\Enums\NextActionUrgency::Upcoming->label() }}
            </x-filament::badge>

            <x-filament::badge color="gray">
                {{ \App\Enums\NextActionUrgency::NoDate->label() }}
            </x-filament::badge>
        </div>
    </div>
</div>
