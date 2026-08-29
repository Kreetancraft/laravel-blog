@include('blog::partials.assets')

@props([
    'model',
    'label' => null,
    'placeholder' => __('Pick a date'),
    'min' => null,
    'max' => null,
    'required' => false,
    'size' => 'sm',
])

<div
    x-data="window.datePicker({
        model: '{{ $model }}',
        min: '{{ $min }}',
        max: '{{ $max }}',
    })"
    x-on:click.outside="isOpen = false"
    class="relative"
>
    <input type="hidden" wire:model="{{ $model }}" x-ref="hidden" />

    <flux:field>
        @if ($label)
            <flux:label :required="$required">{{ $label }}</flux:label>
        @endif

        <div class="relative">
            <input
                type="text"
                x-model="display"
                x-on:click="isOpen = true"
                x-on:keydown.escape="isOpen = false"
                placeholder="{{ $placeholder }}"
                readonly
                class="w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-hidden focus:ring-2 focus:ring-accent focus:ring-offset-2 focus:ring-offset-accent-foreground dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder:text-zinc-500 cursor-pointer"
                :class="{ 'ring-2 ring-accent ring-offset-2 ring-offset-accent-foreground': isOpen }"
            />

            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <flux:icon name="calendar-days" class="size-4 text-zinc-400" />
            </div>
        </div>

        <flux:error name="{{ $model }}" />

        <div
            x-show="isOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute z-50 mt-1 w-72 rounded-xl border border-zinc-200 bg-white p-3 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
            x-on:mousedown.prevent
        >
            <div class="mb-2 flex items-center justify-between">
                <button
                    type="button"
                    x-on:click="prevMonth"
                    class="flex size-8 items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
                >
                    <flux:icon name="chevron-left" class="size-4" />
                </button>

                <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100" x-text="monthYear"></span>

                <button
                    type="button"
                    x-on:click="nextMonth"
                    class="flex size-8 items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
                >
                    <flux:icon name="chevron-right" class="size-4" />
                </button>
            </div>

            <div class="mb-1 grid grid-cols-7 gap-0.5">
                <template x-for="day in dayHeaders" :key="day">
                    <div class="flex h-8 items-center justify-center text-xs font-medium text-zinc-400 dark:text-zinc-500" x-text="day"></div>
                </template>
            </div>

            <div class="grid grid-cols-7 gap-0.5">
                <template x-for="(day, idx) in days" :key="idx">
                    <button
                        type="button"
                        x-show="day !== 0"
                        x-on:click="selectDate(day)"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-sm transition-colors"
                        :class="{
                            'bg-accent text-accent-foreground font-semibold': isSelected(day),
                            'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700': !isSelected(day) && !isDisabled(day),
                            'text-zinc-300 dark:text-zinc-600 cursor-not-allowed': isDisabled(day),
                        }"
                        :disabled="isDisabled(day)"
                        x-text="day"
                    ></button>
                </template>
            </div>

            <div class="mt-2 flex items-center justify-between border-t border-zinc-100 pt-2 dark:border-zinc-700">
                <button
                    type="button"
                    x-on:click="today"
                    class="text-xs font-medium text-sky-600 hover:text-sky-500 dark:text-sky-400 dark:hover:text-sky-300"
                >
                    {{ __('Today') }}
                </button>
                <button
                    type="button"
                    x-on:click="clear"
                    class="text-xs text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                >
                    {{ __('Clear') }}
                </button>
            </div>
        </div>
    </flux:field>
</div>
