<div class="space-y-6">
    <x-blog::compact-table-styles />

    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Posts') }}</flux:heading>
            <flux:subheading>{{ __('Blog posts for the public site.') }}</flux:subheading>
        </div>

        @can('create', \Kreetancraft\Blog\Models\Post::class)
            <flux:button href="{{ \Kreetancraft\Blog\Routes::to('posts.create') }}" variant="primary" icon="plus" size="sm" wire:navigate data-test="create-post">
                {{ __('New post') }}
            </flux:button>
        @endcan
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <flux:badge size="sm" color="zinc">{{ __(':count total', ['count' => $posts->total()]) }}</flux:badge>
        <flux:badge size="sm" color="green">{{ __(':count published', ['count' => $publishedCount]) }}</flux:badge>
    </div>

    <flux:separator />

    <div class="flex flex-wrap items-center gap-3">
        <div class="w-full sm:w-72 relative">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search title or slug...') }}" icon="magnifying-glass" size="sm" />
            <div wire:loading wire:target="search" class="absolute right-3 top-1/2 -translate-y-1/2">
                <flux:icon icon="arrow-path" class="animate-spin size-3.5 text-zinc-400 dark:text-zinc-500" />
            </div>
        </div>

        <flux:select wire:model.live="statusFilter" size="sm" class="w-full sm:w-40">
            <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
            @foreach (\Kreetancraft\Blog\Enums\PostStatus::cases() as $status)
                <flux:select.option value="{{ $status->value }}">{{ $status->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="categoryFilter" size="sm" class="w-full sm:w-44">
            <flux:select.option value="">{{ __('All categories') }}</flux:select.option>
            @foreach ($categoryOptions as $category)
                <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="authorFilter" size="sm" class="w-full sm:w-44">
            <flux:select.option value="">{{ __('All authors') }}</flux:select.option>
            @foreach ($authorOptions as $author)
                <flux:select.option value="{{ $author->id }}">{{ $author->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if ($posts->isEmpty())
        <div class="flex flex-col items-center justify-center p-8 text-center border border-dashed border-zinc-200 dark:border-zinc-800 rounded-lg bg-zinc-50/50 dark:bg-zinc-900/30">
            <flux:icon icon="document-text" class="size-10 text-zinc-400 dark:text-zinc-500 mb-3" />
            <flux:heading size="lg">{{ __('No posts yet') }}</flux:heading>
            <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Create your first blog post to get started.') }}</flux:text>
        </div>
    @else
        <flux:table :paginate="$posts" class="compact-table transition-opacity duration-200" wire:loading.class="opacity-60">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sort === 'title' || $sort === '-title'" :direction="$sort === 'title' ? 'asc' : 'desc'" wire:click="sortBy('title')">{{ __('Title') }}</flux:table.column>
                <flux:table.column>{{ __('Author') }}</flux:table.column>
                <flux:table.column>{{ __('Categories') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sort === 'status' || $sort === '-status'" :direction="$sort === 'status' ? 'asc' : 'desc'" wire:click="sortBy('status')">{{ __('Status') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sort === 'published_at' || $sort === '-published_at'" :direction="$sort === 'published_at' ? 'asc' : 'desc'" wire:click="sortBy('published_at')">{{ __('Published') }}</flux:table.column>
                <flux:table.column>{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($posts as $post)
                    <flux:table.row :key="$post->id">
                        <flux:table.cell class="font-medium max-w-sm whitespace-normal! break-words">
                            <div class="flex items-center gap-2">
                                @can('update', $post)
                                    <flux:link href="{{ \Kreetancraft\Blog\Routes::to('posts.edit', $post) }}" wire:navigate>{{ $post->title }}</flux:link>
                                @else
                                    {{ $post->title }}
                                @endcan
                                @if ($post->is_featured)
                                    <flux:icon icon="star" variant="solid" class="size-3.5 text-amber-400 shrink-0" />
                                @endif
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>{{ $post->author?->name ?? '—' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:text class="text-xs text-zinc-500">{{ $post->categories->pluck('name')->implode(', ') ?: '—' }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$post->status->color()">{{ $post->status->label() }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text class="text-xs text-zinc-500">{{ $post->published_at?->format('M j, Y H:i') ?: '—' }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button icon="ellipsis-vertical" variant="ghost" size="sm" />
                                <flux:menu>
                                    @can('update', $post)
                                        <flux:menu.item href="{{ \Kreetancraft\Blog\Routes::to('posts.edit', $post) }}" icon="pencil-square" wire:navigate>{{ __('Edit') }}</flux:menu.item>
                                    @endcan
                                    @can('delete', $post)
                                        <flux:menu.separator />
                                        <flux:menu.item wire:click="confirmDelete({{ $post->id }})" icon="trash" variant="danger" data-test="delete-post-{{ $post->id }}">{{ __('Delete') }}</flux:menu.item>
                                    @endcan
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif

    <flux:modal name="confirm-delete-post" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete Post?') }}</flux:heading>
                <flux:text class="mt-2">{{ __('The post will be removed from the site. It can be restored from the database if needed.') }}</flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close><flux:button variant="ghost">{{ __('Cancel') }}</flux:button></flux:modal.close>
                <flux:button variant="danger" wire:click="delete" icon="trash">{{ __('Delete Post') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
