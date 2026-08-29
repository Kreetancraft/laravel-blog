<div class="space-y-6">
    <div class="space-y-3">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ \Kreetancraft\Blog\Routes::to('posts') }}" wire:navigate>{{ __('Posts') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $post->title }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <flux:heading size="xl">{{ $post->title }}</flux:heading>
                    <x-blog::form-dirty-indicator :dirty="$formDirty" />
                </div>
                <flux:subheading>{{ __('Edit post.') }}</flux:subheading>
            </div>
            <flux:badge size="sm" :color="$post->status->color()">{{ $post->status->label() }}</flux:badge>
        </div>
    </div>

    <form wire:submit="save" novalidate>
        <div class="space-y-6">
            @include('blog::livewire.partials.post-fields')

            <x-blog::form-error-summary />

            <div class="flex items-center justify-end gap-2">
                <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="save" data-test="save-post" :disabled="$errors->any()">{{ __('Save') }}</flux:button>
            </div>
        </div>
    </form>

    {{-- Mounts the picker the editor's image button opens. trigger => false
         because nothing should be visible here: the toolbar is the trigger, and
         a Choose card below the form would do nothing when clicked. --}}
    @if (\Kreetancraft\Blog\Models\Post::imagesEnabled() && ($picker = config('blog.media_picker_view')))
        @includeIf($picker, ['items' => [], 'group' => 'rich-text-image', 'multiple' => true, 'trigger' => false])
    @endif
</div>
