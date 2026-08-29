@props([
    'model',
    'label' => null,
    'placeholder' => null,
    'min' => null,
    'max' => null,
    'step' => 1,
    'required' => false,
    'suffix' => null,
    'icon' => null,
    // Tighter footprint for use inside table rows, where the default 40px
    // steppers crowd out the surrounding columns.
    'compact' => false,
])

@php
    $control = $compact ? 'size-8' : 'size-10';
    $field = $compact ? 'h-8' : 'h-10';
@endphp

<div
    x-data="window.numberInput({
        model: '{{ $model }}',
        min: {{ $min ?? 'null' }},
        max: {{ $max ?? 'null' }},
        step: {{ $step }},
    })"
    {{ $attributes->merge(['class' => 'w-full']) }}
>
    <input type="hidden" wire:model="{{ $model }}" x-ref="hidden" />

    <flux:field>
        @if ($label)
            <flux:label :required="$required">{{ $label }}</flux:label>
        @endif

        <div class="flex items-center overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
            <button
                type="button"
                x-on:click="decrement"
                x-on:keydown.arrow-down.prevent="decrement"
                class="flex {{ $control }} shrink-0 items-center justify-center text-zinc-400 transition-colors hover:bg-zinc-50 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-30 dark:text-zinc-500 dark:hover:bg-zinc-700 dark:hover:text-zinc-300"
                :disabled="atMin"
                tabindex="-1"
                aria-label="{{ __('Decrease') }}"
            >
                <flux:icon name="minus" class="size-4" />
            </button>

            <div class="relative flex-1 border-x border-zinc-200 dark:border-zinc-700">
                @if ($icon)
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <flux:icon name="{{ $icon }}" class="size-4 text-zinc-400" />
                    </div>
                @endif

                <input
                    type="number"
                    x-model="value"
                    x-on:input="handleInput"
                    x-on:blur="handleBlur"
                    placeholder="{{ $placeholder ?? ($label ? '' : '') }}"
                    {{ $min !== null ? "min={$min}" : '' }}
                    {{ $max !== null ? "max={$max}" : '' }}
                    step="{{ $step }}"
                    class="{{ $field }} w-full bg-transparent text-center text-sm font-medium text-zinc-900 placeholder:text-zinc-400 focus:outline-none dark:text-zinc-100 dark:placeholder:text-zinc-500 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                    :class="{ 'pl-9': '{{ $icon }}' }"
                    x-bind:class="{ 'text-zinc-400': !value && value !== 0 }"
                />
            </div>

            <button
                type="button"
                x-on:click="increment"
                x-on:keydown.arrow-up.prevent="increment"
                class="flex {{ $control }} shrink-0 items-center justify-center text-zinc-400 transition-colors hover:bg-zinc-50 hover:text-zinc-600 disabled:cursor-not-allowed disabled:opacity-30 dark:text-zinc-500 dark:hover:bg-zinc-700 dark:hover:text-zinc-300"
                :disabled="atMax"
                tabindex="-1"
                aria-label="{{ __('Increase') }}"
            >
                <flux:icon name="plus" class="size-4" />
            </button>
        </div>

        @if ($suffix)
            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $suffix }}</div>
        @endif

        <flux:error name="{{ $model }}" />
    </flux:field>
</div>
