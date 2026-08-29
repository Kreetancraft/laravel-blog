{{-- The bundle every shipped form component needs: the editor, the date picker
     and the number input all live in it.

     Included by each of those components rather than by the editor alone. They
     only happen to appear on the same screen today, and a host using the date
     picker on a page without an editor would otherwise get an inert control —
     which is precisely the bug that shipped when the components arrived without
     their JavaScript.

     @once so several components on one page load it once; @assets because a
     bare <script> is not re-executed on wire:navigate, which left the factories
     undefined on any second visit. --}}
@once
    @assets
        <script src="{{ \Kreetancraft\Blog\Http\Controllers\AssetController::editorUrl() }}" defer></script>
    @endassets
@endonce
