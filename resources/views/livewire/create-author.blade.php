<div class="space-y-6">
    <div class="space-y-3">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ \Kreetancraft\Blog\Routes::to('authors') }}" wire:navigate>{{ __('Authors') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ __('New author') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div>
            <div class="flex items-center gap-3">
                <flux:heading size="xl">{{ __('New author') }}</flux:heading>
                <x-blog::form-dirty-indicator :dirty="$formDirty" />
            </div>
            <flux:subheading>{{ __('Add a blog author profile.') }}</flux:subheading>
        </div>
    </div>

    <form wire:submit="save" novalidate>
        <flux:card class="space-y-6">
            @include('blog::livewire.partials.author-fields')

            <x-blog::form-error-summary />

            <div class="flex items-center justify-end gap-2 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="save" data-test="save-author" :disabled="$errors->any()">{{ __('Create author') }}</flux:button>
            </div>
        </flux:card>
    </form>

</div>
