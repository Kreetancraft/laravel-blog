<div class="space-y-6">
    {{-- Header with actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="space-y-1">
            <flux:breadcrumbs>
                <flux:breadcrumbs.item href="{{ \Kreetancraft\Blog\Routes::to('comments') }}" wire:navigate>{{ __('Comments') }}</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ __('Comment #:id', ['id' => $comment->id]) }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>
            <flux:heading size="xl">{{ __('Comment Detail') }}</flux:heading>
        </div>

        <div class="flex items-center gap-2">
            @if ($comment->status !== \Kreetancraft\Blog\Enums\CommentStatus::Approved)
                <flux:button wire:click="setStatus('approved')" size="sm" icon="check" variant="filled" class="bg-emerald-600 hover:bg-emerald-700 text-white dark:bg-emerald-500 dark:hover:bg-emerald-600" data-test="detail-approve">
                    {{ __('Approve') }}
                </flux:button>
            @endif
            @if ($comment->status !== \Kreetancraft\Blog\Enums\CommentStatus::Rejected)
                <flux:button wire:click="setStatus('rejected')" size="sm" icon="x-mark" variant="subtle">
                    {{ __('Reject') }}
                </flux:button>
            @endif
            @if ($comment->status !== \Kreetancraft\Blog\Enums\CommentStatus::Spam)
                <flux:button wire:click="setStatus('spam')" size="sm" icon="shield-exclamation" variant="subtle">
                    {{ __('Spam') }}
                </flux:button>
            @endif
            <flux:button wire:click="delete" wire:confirm="{{ __('Delete this comment permanently?') }}" size="sm" variant="danger" icon="trash">
                {{ __('Delete') }}
            </flux:button>
        </div>
    </div>

    <flux:separator />

    {{-- Two-column layout: Metadata + Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Metadata Card (left) --}}
        <div class="lg:col-span-1 space-y-6">
            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('Author Info') }}</flux:heading>

                <div class="space-y-3">
                    <div>
                        <div class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Name') }}</div>
                        <div class="text-sm font-medium text-zinc-800 dark:text-zinc-200 mt-0.5">{{ $comment->displayName() }}</div>
                    </div>
                    @if ($comment->author_email)
                        <div>
                            <div class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Email') }}</div>
                            <div class="text-sm text-zinc-700 dark:text-zinc-300 mt-0.5">{{ $comment->author_email }}</div>
                        </div>
                    @endif
                    @if ($comment->user)
                        <div>
                            <div class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Account') }}</div>
                            <div class="mt-1"><flux:badge size="sm" color="sky">{{ __('Team Member') }}</flux:badge></div>
                        </div>
                    @endif
                    @if ($comment->author_url)
                        <div>
                            <div class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Website') }}</div>
                            <div class="text-sm mt-0.5">
                                <flux:link href="{{ $comment->author_url }}" target="_blank" rel="noopener nofollow" class="text-sm">{{ Str::limit($comment->author_url, 40) }}</flux:link>
                            </div>
                        </div>
                    @endif
                </div>

                <flux:separator variant="subtle" />

                <flux:heading size="lg">{{ __('Metadata') }}</flux:heading>

                <div class="space-y-3">
                    <div>
                        <div class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('IP Address') }}</div>
                        <div class="text-sm font-mono text-zinc-700 dark:text-zinc-300 mt-0.5 flex items-center gap-1.5">
                            @if ($comment->ip_address)
                                <a href="{{ \Kreetancraft\Blog\Routes::to('comments', ['ipFilter' => $comment->ip_address]) }}" wire:navigate class="hover:underline text-indigo-600 dark:text-indigo-400 font-medium cursor-pointer" title="{{ __('View comments from this IP') }}">
                                    {{ $comment->ip_address }}
                                </a>
                                <span class="text-xs text-zinc-400">({{ $ipCommentCount }})</span>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-500 italic">{{ __('Not recorded') }}</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('User Agent') }}</div>
                        <div class="text-xs font-mono text-zinc-600 dark:text-zinc-400 break-all mt-0.5" title="{{ $comment->user_agent }}">
                            {{ $comment->user_agent ? Str::limit($comment->user_agent, 100) : __('Not recorded') }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">{{ __('Referrer') }}</div>
                        <div class="text-sm mt-0.5">
                            @if ($comment->referrer)
                                <flux:link href="{{ $comment->referrer }}" target="_blank" rel="noopener nofollow" class="text-xs font-mono">{{ Str::limit($comment->referrer, 40) }}</flux:link>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-500 italic text-xs">{{ __('Not recorded') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </flux:card>
        </div>

        {{-- Main Conversation & Action Stream (right) --}}
        <div class="lg:col-span-2 space-y-6">
            <flux:card class="space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <flux:heading size="lg">{{ __('Discussion Thread') }}</flux:heading>
                    @if ($comment->post)
                        <flux:text class="text-xs text-zinc-500">
                            {{ __('On Post:') }} <flux:link href="{{ \Kreetancraft\Blog\Routes::to('posts.edit', $comment->post) }}" wire:navigate class="font-medium">{{ Str::limit($comment->post->title, 40) }}</flux:link>
                        </flux:text>
                    @endif
                </div>

                {{-- Thread Timeline --}}
                <div class="space-y-6">
                    {{-- Parent comment (Context) --}}
                    @if ($comment->parent)
                        <div class="relative pl-6 before:absolute before:left-2 before:top-2 before:bottom-0 before:w-0.5 before:bg-zinc-200 dark:before:bg-zinc-800">
                            <div class="flex items-center gap-2 mb-1">
                                <flux:icon icon="arrow-uturn-right" class="size-3.5 text-zinc-400 -ml-5 bg-white dark:bg-zinc-900 px-0.5" />
                                <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $comment->parent->displayName() }}</span>
                                <span class="text-xs text-zinc-350 dark:text-zinc-700">·</span>
                                <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $comment->parent->created_at->format('M j, Y H:i') }}</span>
                            </div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400 whitespace-pre-line">{{ $comment->parent->content }}</div>
                        </div>
                    @endif

                    {{-- This Comment (Main Highlight) --}}
                    <div class="bg-zinc-50/50 dark:bg-zinc-950/20 border border-zinc-200/60 dark:border-zinc-800/80 rounded-xl p-5 space-y-3">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $comment->displayName() }}</span>
                                <span class="text-xs text-zinc-455 dark:text-zinc-500">{{ $comment->created_at->format('M j, Y H:i') }}</span>
                            </div>
                            <flux:badge size="sm" :color="$comment->status->color()">{{ $comment->status->label() }}</flux:badge>
                        </div>
                        <div class="text-sm text-zinc-850 dark:text-zinc-200 whitespace-pre-line leading-relaxed">{{ $comment->content }}</div>
                    </div>

                    {{-- Replies List --}}
                    @if ($comment->replies->isNotEmpty())
                        <div class="space-y-4 pl-6 relative before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-zinc-200 dark:before:bg-zinc-800">
                            @foreach ($comment->replies as $reply)
                                <div class="relative" wire:key="reply-{{ $reply->id }}">
                                    <div class="absolute -left-[22px] top-1.5 size-2 rounded-full bg-zinc-300 dark:bg-zinc-700 ring-4 ring-white dark:ring-zinc-900"></div>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">{{ $reply->displayName() }}</span>
                                            @if ($reply->user)
                                                <flux:badge size="xs" color="sky">{{ __('Team') }}</flux:badge>
                                            @endif
                                            <span class="text-xs text-zinc-300 dark:text-zinc-700">·</span>
                                            <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $reply->created_at->format('M j, Y H:i') }}</span>
                                            <flux:badge size="xs" :color="$reply->status->color()" variant="subtle">{{ $reply->status->label() }}</flux:badge>
                                        </div>
                                        <div class="text-sm text-zinc-600 dark:text-zinc-400 whitespace-pre-line">{{ $reply->content }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <flux:separator variant="subtle" />

                {{-- Quick Reply Editor --}}
                <div class="space-y-4">
                    <flux:heading size="md">{{ __('Post a Reply') }}</flux:heading>
                    <form wire:submit="sendReply" class="space-y-3" novalidate>
                        <flux:textarea wire:model="replyContent" placeholder="{{ __('Write a reply as :name...', ['name' => auth()->user()->name]) }}" rows="3" />
                        
                        @if ($comment->status === \Kreetancraft\Blog\Enums\CommentStatus::Pending)
                            <flux:text class="text-xs text-zinc-400 italic">{{ __('Replying will automatically approve this comment.') }}</flux:text>
                        @endif

                        <div class="flex items-center gap-2">
                            <flux:button type="submit" variant="primary" icon="paper-airplane" size="sm" wire:loading.attr="disabled" data-test="send-reply">
                                {{ __('Post Reply') }}
                            </flux:button>
                            <div wire:loading wire:target="sendReply">
                                <flux:icon icon="arrow-path" class="animate-spin size-4 text-zinc-400" />
                            </div>
                        </div>
                    </form>
                </div>
            </flux:card>
        </div>
    </div>
</div>
