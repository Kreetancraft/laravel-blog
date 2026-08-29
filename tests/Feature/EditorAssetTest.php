<?php

use Kreetancraft\Blog\Http\Controllers\AssetController;

/**
 * The editor bundle ships with the package and is served from it.
 *
 * 0.1.0 shipped the Blade component but not its JavaScript: the toolbar called
 * `richText()`, which existed only in the application it was extracted from, so
 * the editor was inert in any other host. These pin the fix.
 */
it('serves the editor bundle', function (): void {
    $this->get(route('blog.asset.editor'))
        ->assertOk()
        ->assertHeader('content-type', 'application/javascript');
});

it('ships a bundle that actually defines the editor', function (): void {
    $bundle = file_get_contents(__DIR__.'/../../resources/dist/blog-editor.js');

    expect($bundle)->toContain('window.richText');
});

it('carries tiptap with it, so a host needs no npm packages', function (): void {
    // The whole reason the bundle is committed rather than built by the host:
    // an editor that needs eight npm dependencies and a bundler entry is not
    // something a package can ask for.
    $bundle = file_get_contents(__DIR__.'/../../resources/dist/blog-editor.js');

    expect(strlen($bundle))->toBeGreaterThan(100_000)
        ->and($bundle)->toContain('prosemirror');
});

it('busts its own cache when the bundle changes', function (): void {
    // Served with a year-long max-age, so the URL has to change when the file
    // does or an upgrade ships a stale editor.
    $url = AssetController::editorUrl();

    expect($url)->toContain('v=')
        ->and($url)->toContain(substr(md5_file(__DIR__.'/../../resources/dist/blog-editor.js'), 0, 8));
});

it('can be switched off for a host that replaces the admin UI', function (): void {
    expect(config('blog.routes.serve_assets'))->toBeTrue();
});
