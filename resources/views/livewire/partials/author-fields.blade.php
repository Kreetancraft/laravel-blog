{{-- Shared author fields. --}}
<div class="space-y-6">
    <flux:field>
        <flux:label required>{{ __('Name') }}</flux:label>
        <flux:input wire:model.blur="name" required data-valid="{{ $this->isFieldValid('name') ? 'true' : 'false' }}" />
        <flux:error name="name" />
    </flux:field>

    <x-blog::rich-text model="bio" :label="__('Bio')" :rows="5" />

    @if (\Kreetancraft\Blog\Models\Post::imagesEnabled() && ($picker = config('blog.media_picker_view')))
        @includeIf($picker, ['items' => $avatarMedia, 'group' => 'blog-author-avatar', 'multiple' => false, 'label' => __('Avatar')])
    @endif

    <flux:separator variant="subtle" />

    @include('seo::livewire.partials.seo-fields')
</div>
