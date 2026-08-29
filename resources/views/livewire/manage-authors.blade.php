<div class="space-y-6">
    <x-blog::compact-table-styles />

    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Authors') }}</flux:heading>
            <flux:subheading>{{ __('Writer profiles shown on blog posts.') }}</flux:subheading>
        </div>

        @can('create-blogs')
            <flux:button href="{{ \Kreetancraft\Blog\Routes::to('authors.create') }}" variant="primary" icon="plus" wire:navigate data-test="create-author">
                {{ __('New author') }}
            </flux:button>
        @endcan
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <flux:badge size="sm" color="zinc">{{ __(':count total', ['count' => $authors->total()]) }}</flux:badge>
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

    @if ($authors->isEmpty())
        <div class="flex flex-col items-center justify-center p-8 text-center border border-dashed border-zinc-200 dark:border-zinc-800 rounded-lg bg-zinc-50/50 dark:bg-zinc-900/30">
            <flux:icon icon="user" class="size-10 text-zinc-400 dark:text-zinc-500 mb-3" />
            <flux:heading size="lg">{{ __('No authors yet') }}</flux:heading>
            <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No authors found matching search or none created.') }}</flux:text>
        </div>
    @else
        <flux:table :paginate="$authors" class="compact-table">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sort === 'name' || $sort === '-name'" :direction="$sort === 'name' ? 'asc' : 'desc'" wire:click="sortBy('name')">{{ __('Name') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sort === 'slug' || $sort === '-slug'" :direction="$sort === 'slug' ? 'asc' : 'desc'" wire:click="sortBy('slug')">{{ __('Slug') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sort === 'posts_count' || $sort === '-posts_count'" :direction="$sort === 'posts_count' ? 'asc' : 'desc'" wire:click="sortBy('posts_count')">{{ __('Posts') }}</flux:table.column>
                <flux:table.column>{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($authors as $author)
                    <flux:table.row :key="$author->id">
                        <flux:table.cell class="font-medium max-w-xs whitespace-normal! break-words">
                            <div class="flex items-center gap-2">
                                @if ($avatarUrl = $author->avatarUrl())
                                    <img src="{{ $avatarUrl }}" class="size-7 rounded-full object-cover ring-1 ring-zinc-200 dark:ring-zinc-700" alt="" />
                                @else
                                    <div class="flex size-7 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                        <flux:icon icon="user" class="size-4 text-zinc-400" />
                                    </div>
                                @endif
                                @can('edit-blogs')
                                    <flux:link href="{{ \Kreetancraft\Blog\Routes::to('authors.edit', $author) }}" wire:navigate>{{ $author->name }}</flux:link>
                                @else
                                    {{ $author->name }}
                                @endcan
                            </div>
                        </flux:table.cell>
                        <flux:table.cell><flux:text class="text-xs text-zinc-500">{{ $author->slug }}</flux:text></flux:table.cell>
                        <flux:table.cell>{{ $author->posts_count }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button icon="ellipsis-vertical" variant="ghost" size="sm" />
                                <flux:menu>
                                    @can('edit-blogs')
                                        <flux:menu.item href="{{ \Kreetancraft\Blog\Routes::to('authors.edit', $author) }}" icon="pencil-square" wire:navigate>{{ __('Edit') }}</flux:menu.item>
                                    @endcan
                                    @can('delete-blogs')
                                        <flux:menu.separator />
                                        <flux:menu.item wire:click="delete({{ $author->id }})" wire:confirm="{{ __('Delete this author?') }}" icon="trash" variant="danger" data-test="delete-author-{{ $author->id }}">{{ __('Delete') }}</flux:menu.item>
                                    @endcan
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>
