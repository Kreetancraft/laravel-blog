<?php

namespace Kreetancraft\Blog\Http\Controllers;

use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves the editor bundle straight from the package.
 *
 * Publishing to public/ would work, but it makes every upgrade a two-step that
 * fails silently when the second step is forgotten — the same trap as a
 * published config going stale. Serving from here means `composer update` is
 * the whole upgrade.
 *
 * The bundle carries Tiptap with it, so a host needs no npm packages, no build
 * step and no entry in its own bundler config.
 */
class AssetController extends Controller
{
    public function editor(): Response
    {
        $path = __DIR__.'/../../../resources/dist/blog-editor.js';

        abort_unless(is_file($path), 404);

        return (new BinaryFileResponse($path))
            ->setPublic()
            // The URL carries a hash of the file, so it can be cached hard:
            // a new build is a new URL rather than a stale asset.
            ->setMaxAge(31536000)
            ->setImmutable();
    }

    /**
     * URL of the editor bundle, cache-busted by its own contents.
     */
    public static function editorUrl(): string
    {
        $path = __DIR__.'/../../../resources/dist/blog-editor.js';

        $version = is_file($path) ? substr((string) md5_file($path), 0, 8) : 'missing';

        return route(config('blog.routes.names.asset', 'blog.asset.editor'), ['v' => $version]);
    }
}
