{{-- Shared post fields. Expects: $authors, $seriesOptions, $categoryOptions, $tagOptions. --}}
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <flux:card class="space-y-6">
            <flux:heading size="lg">{{ __('Content') }}</flux:heading>

            <flux:field>
                <flux:label required>{{ __('Title') }}</flux:label>
                <flux:input wire:model.blur="title" required data-valid="{{ $this->isFieldValid('title') ? 'true' : 'false' }}" />
                <flux:error name="title" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Slug') }}</flux:label>
                <flux:input wire:model.blur="slug" :description="__('Leave blank to auto-generate from the title.')" />
                <flux:error name="slug" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Excerpt') }}</flux:label>
                <flux:textarea wire:model.blur="excerpt" rows="3" :description="__('Short summary shown on listing pages and used as the default meta description.')" />
                <flux:error name="excerpt" />
            </flux:field>

            <x-blog::rich-text model="content" :label="__('Content')" :rows="12" />
        </flux:card>

        @include('seo::livewire.partials.seo-fields')
    </div>

    <div class="space-y-6">
        <flux:card class="space-y-6">
            <flux:heading size="lg">{{ __('Publishing') }}</flux:heading>

            <flux:select wire:model.live="status" :label="__('Status')">
                @foreach (\Kreetancraft\Blog\Enums\PostStatus::cases() as $statusOption)
                    <flux:select.option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</flux:select.option>
                @endforeach
            </flux:select>

            <x-blog::date-picker model="published_at" :label="__('Publish date')"
                :description="$status === 'scheduled' ? __('Required — the post goes live at this time.') : __('Leave blank to use the moment of publishing.')" />

            <flux:switch wire:model.live="is_featured" :label="__('Featured post')" />
        </flux:card>

        <flux:card class="space-y-6">
            <flux:heading size="lg">{{ __('Organisation') }}</flux:heading>

            <flux:field>
                <flux:label>{{ __('Author') }}</flux:label>
                <flux:select wire:model.blur="author_id" :placeholder="__('Select an author')">
                @foreach ($authors as $author)
                    <flux:select.option value="{{ $author->id }}">{{ $author->name }}</flux:select.option>
                @endforeach
            </flux:select>
                <flux:error name="author_id" />
            </flux:field>

            <flux:select wire:model.live="series_id" :label="__('Series (optional)')">
                <flux:select.option value="">{{ __('None') }}</flux:select.option>
                @foreach ($seriesOptions as $seriesOption)
                    <flux:select.option value="{{ $seriesOption->id }}">{{ $seriesOption->title }}</flux:select.option>
                @endforeach
            </flux:select>

            @if ($series_id)
                <x-blog::number-input model="order_in_series" :label="__('Order in series')" :min="0" />
            @endif

            <flux:field>
                <flux:label>{{ __('Categories') }}</flux:label>
                <x-blog::chip-checkbox-group model="categories" :options="$categoryOptions->pluck('name', 'id')->all()" />
                <flux:error name="categories" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Tags') }}</flux:label>
                <x-blog::chip-checkbox-group model="tags" :options="$tagOptions->pluck('name', 'id')->all()" />
                <flux:error name="tags" />
            </flux:field>
        </flux:card>

        @if (\Kreetancraft\Blog\Models\Post::imagesEnabled() && ($picker = config('blog.media_picker_view')))
            @includeIf($picker, ['items' => $featuredMedia, 'group' => 'blog-post-featured', 'multiple' => false, 'label' => __('Featured image')])
        @endif
    </div>
</div>
