<div class="space-y-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="\Kreetancraft\Blog\Layout::home()" wire:navigate>{{ __('Dashboard') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="\Kreetancraft\Blog\Routes::to('categories')" wire:navigate>{{ __('Categories') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ __('Edit') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <form wire:submit="save" class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1">
                <flux:heading size="xl" level="1">{{ __('Edit category') }}</flux:heading>
                <flux:subheading class="max-w-xl">{{ __('Group posts under a topic. Its own meta drives the category page in search.') }}</flux:subheading>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <flux:modal.trigger name="confirm-delete">
                    <flux:button type="button" variant="danger" icon="trash">{{ __('Delete') }}</flux:button>
                </flux:modal.trigger>
                <flux:button type="submit" variant="primary" icon="check">{{ __('Save') }}</flux:button>
            </div>
        </div>

        <x-blog::form-error-summary />

        @include('blog::livewire.partials.category-fields')
    </form>

    <flux:modal name="confirm-delete" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Delete this category?') }}</flux:heading>
            <flux:subheading>{{ __('Posts keep their other taxonomy; this one is removed from them.') }}</flux:subheading>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="delete" variant="danger" icon="trash">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
