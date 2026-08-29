<div class="space-y-6">
    <div class="space-y-3">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ \Kreetancraft\Blog\Routes::to('series') }}" wire:navigate>{{ __('Series') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ __('New series') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div>
            <div class="flex items-center gap-3">
                <flux:heading size="xl">{{ __('New series') }}</flux:heading>
                <x-blog::form-dirty-indicator :dirty="$formDirty" />
            </div>
            <flux:subheading>{{ __('Group related posts into an ordered series.') }}</flux:subheading>
        </div>
    </div>

    <form wire:submit="save" novalidate>
        <flux:card class="space-y-6">
            @include('blog::livewire.partials.series-fields')

            <x-blog::form-error-summary />

            <div class="flex items-center justify-end gap-2 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="save" data-test="save-series" :disabled="$errors->any()">{{ __('Create series') }}</flux:button>
            </div>
        </flux:card>
    </form>

    {{-- The picker is the host's: this package ships no image handling, and
         which picker you use is your choice. Point `blog.media_picker_view`
         at a view of yours and it renders here. --}}
    @if (\Kreetancraft\Blog\Models\Post::imagesEnabled() && ($picker = config('blog.media_picker_view')))
        @includeIf($picker, ['items' => [], 'group' => 'rich-text-image', 'multiple' => true])
    @endif
</div>
