<div class="space-y-6">
    <x-blog::compact-table-styles />

    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Categories') }}</flux:heading>
            <flux:subheading>{{ __('Group blog posts by topic.') }}</flux:subheading>
        </div>

        @can('edit-blogs')
            <flux:button wire:click="openCreate" variant="primary" icon="plus" data-test="create-category">{{ __('New category') }}</flux:button>
        @endcan
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <flux:badge size="sm" color="zinc">{{ __(':count total', ['count' => $categories->total()]) }}</flux:badge>
    </div>

    <flux:separator />

    <div class="flex flex-wrap items-center gap-3">
        <div class="w-full sm:w-72 relative">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search name or slug...') }}" icon="magnifying-glass" size="sm" />
            <div wire:loading wire:target="search" class="absolute right-3 top-1/2 -translate-y-1/2">
                <flux:icon icon="arrow-path" class="animate-spin size-3.5 text-zinc-400 dark:text-zinc-500" />
            </div>
        </div>
    </div>

    @if ($categories->isEmpty())
        <div class="flex flex-col items-center justify-center p-8 text-center border border-dashed border-zinc-200 dark:border-zinc-800 rounded-lg bg-zinc-50/50 dark:bg-zinc-900/30">
            <flux:icon icon="folder-open" class="size-10 text-zinc-400 dark:text-zinc-500 mb-3" />
            <flux:heading size="lg">{{ __('No categories yet') }}</flux:heading>
            <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No categories found matching search or none created.') }}</flux:text>
        </div>
    @else
        <flux:table :paginate="$categories" class="compact-table">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sort === 'name' || $sort === '-name'" :direction="$sort === 'name' ? 'asc' : 'desc'" wire:click="sortBy('name')">{{ __('Name') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sort === 'slug' || $sort === '-slug'" :direction="$sort === 'slug' ? 'asc' : 'desc'" wire:click="sortBy('slug')">{{ __('Slug') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sort === 'posts_count' || $sort === '-posts_count'" :direction="$sort === 'posts_count' ? 'asc' : 'desc'" wire:click="sortBy('posts_count')">{{ __('Posts') }}</flux:table.column>
                <flux:table.column>{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($categories as $category)
                    <flux:table.row :key="$category->id">
                        <flux:table.cell class="font-medium max-w-xs whitespace-normal! break-words">{{ $category->name }}</flux:table.cell>
                        <flux:table.cell><flux:text class="text-xs text-zinc-500">{{ $category->slug }}</flux:text></flux:table.cell>
                        <flux:table.cell>{{ $category->posts_count }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button icon="ellipsis-vertical" variant="ghost" size="sm" />
                                <flux:menu>
                                    @can('edit-blogs')
                                        <flux:menu.item wire:click="openEdit({{ $category->id }})" icon="pencil-square">{{ __('Edit') }}</flux:menu.item>
                                    @endcan
                                    @can('edit-blogs')
                                        <flux:menu.separator />
                                        <flux:menu.item wire:click="delete({{ $category->id }})" wire:confirm="{{ __('Delete this category? Posts keep existing but lose the category.') }}" icon="trash" variant="danger" data-test="delete-category-{{ $category->id }}">{{ __('Delete') }}</flux:menu.item>
                                    @endcan
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    <flux:modal name="category-form" class="md:w-lg">
        <form wire:submit="save" class="space-y-6" novalidate>
            <flux:heading size="lg">{{ $editingId ? __('Edit category') : __('New category') }}</flux:heading>

            <flux:input wire:model="name" :label="__('Name')" required />
            <flux:textarea wire:model="description" :label="__('Description')" rows="3" />

            <flux:separator variant="subtle" />

            @include('seo::livewire.partials.seo-fields', ['compact' => true])

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:loading.class="opacity-60" data-test="save-category">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
