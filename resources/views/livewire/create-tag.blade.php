<div class="space-y-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="\Kreetancraft\Blog\Layout::home()" wire:navigate>{{ __('Dashboard') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item :href="\Kreetancraft\Blog\Routes::to('tags')" wire:navigate>{{ __('Tags') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ __('New') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <form wire:submit="save" class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1">
                <flux:heading size="xl" level="1">{{ __('New tag') }}</flux:heading>
                <flux:subheading class="max-w-xl">{{ __('A lighter label than a category, for cross-cutting subjects.') }}</flux:subheading>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <flux:button type="submit" variant="primary" icon="check">{{ __('Save') }}</flux:button>
            </div>
        </div>

        <x-blog::form-error-summary />

        @include('blog::livewire.partials.tag-fields')
    </form>
</div>
