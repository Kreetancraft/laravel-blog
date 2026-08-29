<div class="space-y-6">
    <x-blog::compact-table-styles />

    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Comments') }}</flux:heading>
            <flux:subheading>{{ __('Moderate and reply to reader comments on blog posts.') }}</flux:subheading>
        </div>
    </div>

    <flux:separator />

    {{-- Clean & elegant horizontal navbar for status filtering --}}
    <flux:navbar class="-mb-px border-b border-zinc-200 dark:border-zinc-800 pb-px mb-4">
        <flux:navbar.item wire:click="setStatusTab('')" :current="$statusFilter === ''" class="cursor-pointer">
            {{ __('All') }}
            <flux:badge size="sm" class="ml-1.5" color="zinc">{{ $statusCounts->sum() }}</flux:badge>
        </flux:navbar.item>

        @foreach (\Kreetancraft\Blog\Enums\CommentStatus::cases() as $status)
            @php
                $badgeColors = [
                    'pending' => 'amber',
                    'approved' => 'emerald',
                    'spam' => 'red',
                    'rejected' => 'zinc'
                ];
            @endphp
            <flux:navbar.item wire:click="setStatusTab('{{ $status->value }}')"
                              :current="$statusFilter === $status->value"
                              class="cursor-pointer"
                              data-test="tab-{{ $status->value }}">
                {{ $status->label() }}
                <flux:badge size="sm" class="ml-1.5" :color="$badgeColors[$status->value] ?? 'zinc'">
                    {{ $statusCounts->get($status->value, 0) }}
                </flux:badge>
            </flux:navbar.item>
        @endforeach
    </flux:navbar>

    {{-- IP Filter Banner --}}
    @if ($ipFilter)
        <flux:callout variant="warning" icon="funnel" heading="{{ __('Showing comments from IP') }} {{ $ipFilter }}">
            <flux:button wire:click="clearIpFilter" size="xs" variant="ghost" class="ml-2">{{ __('Clear filter') }}</flux:button>
        </flux:callout>
    @endif

    <div class="flex flex-wrap items-center gap-3">
        <div class="w-full sm:w-72 relative">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search name, email, or content...') }}" icon="magnifying-glass" size="sm" />
            <div wire:loading wire:target="search" class="absolute right-3 top-1/2 -translate-y-1/2">
                <flux:icon icon="arrow-path" class="animate-spin size-3.5 text-zinc-400 dark:text-zinc-500" />
            </div>
        </div>

        <flux:select wire:model.live="postFilter" size="sm" class="w-full sm:w-64">
            <flux:select.option value="">{{ __('All posts') }}</flux:select.option>
            @foreach ($postOptions as $postOption)
                <flux:select.option value="{{ $postOption->id }}">{{ \Illuminate\Support\Str::limit($postOption->title, 50) }}</flux:select.option>
            @endforeach
        </flux:select>

        @if (count($selected) > 0)
            <flux:spacer />
            <flux:text class="text-sm text-zinc-500">{{ __(':count selected', ['count' => count($selected)]) }}</flux:text>
            <flux:button wire:click="bulkSetStatus('approved')" size="sm" icon="check" data-test="bulk-approve">{{ __('Approve') }}</flux:button>
            <flux:button wire:click="bulkSetStatus('rejected')" size="sm" icon="x-mark" data-test="bulk-reject">{{ __('Reject') }}</flux:button>
            <flux:button wire:click="bulkSetStatus('spam')" size="sm" icon="shield-exclamation" data-test="bulk-spam">{{ __('Spam') }}</flux:button>
            <flux:button wire:click="bulkDelete" wire:confirm="{{ __('Delete the selected comments permanently?') }}" size="sm" variant="danger" icon="trash" data-test="bulk-delete">{{ __('Delete') }}</flux:button>
        @endif
    </div>

    @if ($comments->isEmpty())
        <div class="flex flex-col items-center justify-center p-8 text-center border border-dashed border-zinc-200 dark:border-zinc-800 rounded-lg bg-zinc-50/50 dark:bg-zinc-900/30">
            <flux:icon icon="chat-bubble-left-right" class="size-10 text-zinc-400 dark:text-zinc-500 mb-3" />
            <flux:heading size="lg">{{ __('No comments') }}</flux:heading>
            <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Nothing matches the current filters.') }}</flux:text>
        </div>
    @else
        <flux:table :paginate="$comments" class="compact-table transition-opacity duration-200" wire:loading.class="opacity-60">
            <flux:table.columns>
                <flux:table.column>
                    <flux:checkbox wire:model.live="selectPage" data-test="select-page" />
                </flux:table.column>
                <flux:table.column>{{ __('Author') }}</flux:table.column>
                <flux:table.column>{{ __('Comment') }}</flux:table.column>
                <flux:table.column>{{ __('Post') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
                <flux:table.column>{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($comments as $comment)
                    <flux:table.row :key="$comment->id">
                        <flux:table.cell>
                            <flux:checkbox wire:model.live="selected" value="{{ $comment->id }}" />
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="font-medium text-zinc-800 dark:text-zinc-200">{{ $comment->displayName() }}</div>
                            @if ($comment->author_email)
                                <flux:text class="text-xs text-zinc-500">{{ $comment->author_email }}</flux:text>
                            @elseif ($comment->user)
                                <flux:badge size="sm" color="sky">{{ __('Team') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="max-w-sm whitespace-normal! break-words">
                            @if ($comment->parent)
                                <flux:text class="text-xs text-zinc-400 italic">{{ __('Replying to :name', ['name' => $comment->parent->displayName()]) }}</flux:text>
                            @endif
                            <a href="{{ \Kreetancraft\Blog\Routes::to('comments.show', $comment) }}" wire:navigate class="block text-start cursor-pointer" data-test="view-comment-{{ $comment->id }}">
                                <flux:text class="text-sm text-zinc-800 dark:text-zinc-200 hover:underline">{{ \Illuminate\Support\Str::limit($comment->content, 120) }}</flux:text>
                            </a>
                            @if ($comment->replies_count > 0)
                                <flux:text class="text-xs text-zinc-400">{{ trans_choice(':count reply|:count replies', $comment->replies_count, ['count' => $comment->replies_count]) }}</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($comment->post)
                                @can('edit-blogs')
                                    <flux:link href="{{ \Kreetancraft\Blog\Routes::to('posts.edit', $comment->post) }}" wire:navigate class="text-sm">{{ \Illuminate\Support\Str::limit($comment->post->title, 40) }}</flux:link>
                                @else
                                    <flux:text class="text-sm text-zinc-800 dark:text-zinc-200">{{ \Illuminate\Support\Str::limit($comment->post->title, 40) }}</flux:text>
                                @endcan
                            @else
                                —
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$comment->status->color()">{{ $comment->status->label() }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text class="text-xs text-zinc-500">{{ $comment->created_at->format('M j, Y H:i') }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button icon="ellipsis-vertical" variant="ghost" size="sm" />
                                <flux:menu>
                                    <flux:menu.item href="{{ \Kreetancraft\Blog\Routes::to('comments.show', $comment) }}" icon="eye" wire:navigate>{{ __('View Detail') }}</flux:menu.item>

                                    @if ($comment->status !== \Kreetancraft\Blog\Enums\CommentStatus::Approved)
                                        <flux:menu.item wire:click="setStatus({{ $comment->id }}, 'approved')" icon="check" data-test="approve-comment-{{ $comment->id }}">{{ __('Approve') }}</flux:menu.item>
                                    @endif
                                    @if ($comment->status !== \Kreetancraft\Blog\Enums\CommentStatus::Rejected)
                                        <flux:menu.item wire:click="setStatus({{ $comment->id }}, 'rejected')" icon="x-mark">{{ __('Reject') }}</flux:menu.item>
                                    @endif
                                    @if ($comment->status !== \Kreetancraft\Blog\Enums\CommentStatus::Spam)
                                        <flux:menu.item wire:click="setStatus({{ $comment->id }}, 'spam')" icon="shield-exclamation">{{ __('Mark Spam') }}</flux:menu.item>
                                    @endif

                                    <flux:menu.separator />
                                    <flux:menu.item wire:click="delete({{ $comment->id }})" wire:confirm="{{ __('Delete this comment permanently?') }}" icon="trash" variant="danger" data-test="delete-comment-{{ $comment->id }}">{{ __('Delete') }}</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>
