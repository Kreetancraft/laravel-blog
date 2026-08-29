{{-- Shared fields for the category Create/Edit pages. --}}
<div class="space-y-6">
    <flux:card class="space-y-6">
        <flux:input wire:model.blur="name" :label="__('Name')" required autofocus />
        <flux:error name="name" />

        <flux:textarea wire:model.blur="description" :label="__('Description')" rows="4" />
        <flux:error name="description" />
    </flux:card>

    @include('seo::livewire.partials.seo-fields')
</div>
