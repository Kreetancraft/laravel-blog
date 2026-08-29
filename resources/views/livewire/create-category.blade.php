<div class="space-y-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="\Kreetancraft\Blog\Layout::home()" wire:navigate>{{ __('Dashboard') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="\Kreetancraft\Blog\Routes::to('categories')" wire:navigate>{{ __('Categories') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ __('New') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <form wire:submit="save" class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1">
                <flux:heading size="xl" level="1">{{ __('New category') }}</flux:heading>
                <flux:subheading class="max-w-xl">{{ __('Group posts under a topic. Its own meta drives the category page in search.') }}</flux:subheading>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <flux:button type="submit" variant="primary" icon="check">{{ __('Save') }}</flux:button>
            </div>
        </div>

        <x-blog::form-error-summary />

        @include('blog::livewire.partials.category-fields')
    </form>
</div>
