<div class="space-y-6">
    <div class="space-y-3">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ \Kreetancraft\Blog\Routes::to('posts') }}" wire:navigate>{{ __('Posts') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ __('New post') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div>
            <div class="flex items-center gap-3">
                <flux:heading size="xl">{{ __('New post') }}</flux:heading>
                <x-blog::form-dirty-indicator :dirty="$formDirty" />
            </div>
            <flux:subheading>{{ __('Write a new blog post.') }}</flux:subheading>
        </div>
    </div>

    <form wire:submit="save" novalidate>
        <div class="space-y-6">
            @include('blog::livewire.partials.post-fields')

            <x-blog::form-error-summary />

            <div class="flex items-center justify-end gap-2">
                <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="save" data-test="save-post" :disabled="$errors->any()">{{ __('Create post') }}</flux:button>
            </div>
        </div>
    </form>

</div>
