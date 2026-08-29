<div class="space-y-6">
    <x-blog::compact-table-styles />

    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Series') }}</flux:heading>
            <flux:subheading>{{ __('Ordered collections of related posts.') }}</flux:subheading>
        </div>

        @can('create-blogs')
            <flux:button href="{{ \Kreetancraft\Blog\Routes::to('series.create') }}" variant="primary" icon="plus" wire:navigate data-test="create-series">
                {{ __('New series') }}
            </flux:button>
        @endcan
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <flux:badge size="sm" color="zinc">{{ __(':count total', ['count' => $seriesList->total()]) }}</flux:badge>
    </div>

    <flux:separator />

    <div class="flex flex-wrap items-center gap-3">
        <div class="w-full sm:w-72 relative">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search title or slug...') }}" icon="magnifying-glass" size="sm" />
            <div wire:loading wire:target="search" class="absolute right-3 top-1/2 -translate-y-1/2">
                <flux:icon icon="arrow-path" class="animate-spin size-3.5 text-zinc-400 dark:text-zinc-500" />
            </div>
        </div>
    </div>

    @if ($seriesList->isEmpty())
        <div class="flex flex-col items-center justify-center p-8 text-center border border-dashed border-zinc-200 dark:border-zinc-800 rounded-lg bg-zinc-50/50 dark:bg-zinc-900/30">
            <flux:icon icon="rectangle-stack" class="size-10 text-zinc-400 dark:text-zinc-500 mb-3" />
            <flux:heading size="lg">{{ __('No series yet') }}</flux:heading>
            <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No series found matching search or none created.') }}</flux:text>
        </div>
    @else
        <flux:table :paginate="$seriesList" class="compact-table">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sort === 'title' || $sort === '-title'" :direction="$sort === 'title' ? 'asc' : 'desc'" wire:click="sortBy('title')">{{ __('Title') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sort === 'status' || $sort === '-status'" :direction="$sort === 'status' ? 'asc' : 'desc'" wire:click="sortBy('status')">{{ __('Status') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sort === 'posts_count' || $sort === '-posts_count'" :direction="$sort === 'posts_count' ? 'asc' : 'desc'" wire:click="sortBy('posts_count')">{{ __('Posts') }}</flux:table.column>
                <flux:table.column>{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($seriesList as $series)
                    <flux:table.row :key="$series->id">
                        <flux:table.cell class="font-medium max-w-xs whitespace-normal! break-words">
                            @can('edit-blogs')
                                <flux:link href="{{ \Kreetancraft\Blog\Routes::to('series.edit', $series) }}" wire:navigate>{{ $series->title }}</flux:link>
                            @else
                                {{ $series->title }}
                            @endcan
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$series->status->color()">{{ $series->status->label() }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $series->posts_count }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button icon="ellipsis-vertical" variant="ghost" size="sm" />
                                <flux:menu>
                                    @can('edit-blogs')
                                        <flux:menu.item href="{{ \Kreetancraft\Blog\Routes::to('series.edit', $series) }}" icon="pencil-square" wire:navigate>{{ __('Edit') }}</flux:menu.item>
                                    @endcan
                                    @can('delete-blogs')
                                        <flux:menu.separator />
                                        <flux:menu.item wire:click="delete({{ $series->id }})" wire:confirm="{{ __('Delete this series? Posts keep existing but leave the series.') }}" icon="trash" variant="danger" data-test="delete-series-{{ $series->id }}">{{ __('Delete') }}</flux:menu.item>
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
