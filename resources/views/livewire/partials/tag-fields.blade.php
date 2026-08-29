{{-- Shared fields for the tag Create/Edit pages. --}}
<div class="space-y-6">
    <flux:card class="space-y-6">
        <flux:input wire:model.blur="name" :label="__('Name')" required autofocus />
        <flux:error name="name" />
    </flux:card>
</div>
