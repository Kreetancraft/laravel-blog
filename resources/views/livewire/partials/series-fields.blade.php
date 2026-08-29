{{-- Shared series fields. --}}
<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <flux:field>
            <flux:label required>{{ __('Title') }}</flux:label>
            <flux:input wire:model.blur="title" required data-valid="{{ $this->isFieldValid('title') ? 'true' : 'false' }}" />
            <flux:error name="title" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('Status') }}</flux:label>
            <flux:select wire:model.blur="status">
            @foreach (\Kreetancraft\Blog\Enums\SeriesStatus::cases() as $statusOption)
                <flux:select.option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</flux:select.option>
            @endforeach
        </flux:select>
                <flux:error name="status" />
            </flux:field>
    </div>

    <x-blog::rich-text model="description" :label="__('Description')" :rows="4" />

    @if (\Kreetancraft\Blog\Models\Post::imagesEnabled() && ($picker = config('blog.media_picker_view')))
        @includeIf($picker, ['items' => $coverMedia, 'group' => 'blog-series-cover', 'multiple' => false, 'label' => __('Cover image')])
    @endif

    <flux:separator variant="subtle" />

    @include('seo::livewire.partials.seo-fields')
</div>
