<div class="space-y-6">
    <div class="space-y-3">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ \Kreetancraft\Blog\Routes::to('series') }}" wire:navigate>{{ __('Series') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $series->title }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <flux:heading size="xl">{{ $series->title }}</flux:heading>
                    <x-blog::form-dirty-indicator :dirty="$formDirty" />
                </div>
                <flux:subheading>{{ __('Edit series.') }}</flux:subheading>
            </div>
            <flux:badge size="sm" :color="$series->status->color()">{{ $series->status->label() }}</flux:badge>
        </div>
    </div>

    <form wire:submit="save" novalidate>
        <flux:card class="space-y-6">
            @include('blog::livewire.partials.series-fields')

            <x-blog::form-error-summary />

            <div class="flex items-center justify-end gap-2 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="save" data-test="save-series" :disabled="$errors->any()">{{ __('Save') }}</flux:button>
            </div>
        </flux:card>
    </form>

    <flux:card class="space-y-4">
        <flux:heading size="lg">{{ __('Posts in this series') }}</flux:heading>

        @if ($seriesPosts->isEmpty())
            <flux:text class="text-sm text-zinc-500">{{ __('No posts yet. Assign posts to this series from the post edit screen.') }}</flux:text>
        @else
            <flux:table class="compact-table">
                <flux:table.columns>
                    <flux:table.column>{{ __('Order') }}</flux:table.column>
                    <flux:table.column>{{ __('Title') }}</flux:table.column>
                    <flux:table.column>{{ __('Author') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($seriesPosts as $seriesPost)
                        <flux:table.row :key="$seriesPost->id">
                            <flux:table.cell>{{ $seriesPost->order_in_series ?? '—' }}</flux:table.cell>
                            <flux:table.cell class="font-medium">
                                @can('edit-blogs')
                                    <flux:link href="{{ \Kreetancraft\Blog\Routes::to('posts.edit', $seriesPost) }}" wire:navigate>{{ $seriesPost->title }}</flux:link>
                                @else
                                    {{ $seriesPost->title }}
                                @endcan
                            </flux:table.cell>
                            <flux:table.cell>{{ $seriesPost->author?->name ?? '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$seriesPost->status->color()">{{ $seriesPost->status->label() }}</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

</div>
