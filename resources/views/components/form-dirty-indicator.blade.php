@props(['dirty' => false])

{{-- The server flag covers "synced but not saved"; when it is off, wire:dirty
     shows the badge instantly on client-side edits before the first sync. --}}
<span {{ $attributes }}>
    @if ($dirty)
        <flux:badge color="amber" size="sm">{{ __('Unsaved changes') }}</flux:badge>
    @else
        <flux:badge color="amber" size="sm" wire:dirty>{{ __('Unsaved changes') }}</flux:badge>
    @endif
</span>
