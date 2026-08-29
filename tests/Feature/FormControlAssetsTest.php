<?php

/**
 * The shipped form components need JavaScript, and it has to reach the page.
 *
 * date-picker and number-input came out of an application whose app.js defined
 * window.datePicker and window.numberInput. They shipped as Blade with no
 * JavaScript, so both were inert in every other host — the same defect the
 * editor had in 0.1.0, missed on these two.
 */
it('bundles every factory its components call', function (): void {
    $bundle = file_get_contents(__DIR__.'/../../resources/dist/blog-editor.js');

    foreach (['richText', 'datePicker', 'numberInput'] as $factory) {
        expect($bundle)->toContain('window.'.$factory);
    }
});

it('loads the bundle from each component that needs it', function (): void {
    // They only happen to share a screen today. A host using the date picker on
    // a page with no editor would otherwise get an inert control.
    foreach (['rich-text', 'date-picker', 'number-input'] as $component) {
        $blade = file_get_contents(__DIR__.'/../../resources/views/components/'.$component.'.blade.php');

        expect($blade)->toContain('blog::partials.assets');
    }
});

it('loads it once per page and survives wire:navigate', function (): void {
    $partial = file_get_contents(__DIR__.'/../../resources/views/partials/assets.blade.php');

    // @once: several of these components can sit on one screen.
    // @assets: a bare script is not re-executed on navigate, which left the
    // factories undefined on a second visit.
    expect($partial)->toContain('@once')
        ->toContain('@assets')
        ->toContain('AssetController::editorUrl');
});

it('calls each factory with the name the bundle defines', function (): void {
    // A rename on either side is silent: the control simply never initialises.
    $expected = [
        'rich-text' => 'richText(',
        'date-picker' => 'datePicker(',
        'number-input' => 'numberInput(',
    ];

    foreach ($expected as $component => $call) {
        $blade = file_get_contents(__DIR__.'/../../resources/views/components/'.$component.'.blade.php');

        expect($blade)->toContain($call);
    }
});
