@props(['model', 'options' => [], 'class' => 'flex flex-wrap gap-2'])

<div class="{{ $class }}">
    @foreach ($options as $value => $label)
        <label class="relative flex cursor-pointer items-center justify-center rounded-lg border border-zinc-200 bg-zinc-50/50 p-2 transition-colors hover:bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-900/40 dark:hover:bg-zinc-800/80 group has-[:checked]:border-sky-500/50 dark:has-[:checked]:border-sky-500/50 has-[:checked]:bg-sky-500/10 dark:has-[:checked]:bg-sky-500/15">
            <input type="checkbox" value="{{ $value }}" wire:model="{{ $model }}" class="sr-only" />
            <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400 group-hover:text-zinc-900 dark:group-hover:text-zinc-200 group-has-[:checked]:text-sky-600 dark:group-has-[:checked]:text-sky-400">{{ __($label) }}</span>
        </label>
    @endforeach
</div>
