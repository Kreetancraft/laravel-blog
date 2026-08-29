# Laravel Blog

Posts, categories, tags, authors, series and moderated comments, with SEO metadata and a public
read API. Livewire 4 + Flux UI.

Ships no layout, no CSS, no user model and no image handling — it renders into your application
and works with whatever you already have.

## Design decisions worth knowing before you install

**It does not care which user model you have.** Comments resolve their author through
`config('auth.providers.users.model')`; every policy type-hints `Authenticatable`.

**It names no permission of its own.** Its screens ask the ordinary authorization question and
its policies answer it. Until permissions exist anywhere in the app they are open, so it works
on a bare install rather than failing closed.

**It ships no image handling.** Featured images, author avatars and series covers resolve
through `blog.image_resolver`. With none configured they return null and the pickers hide
themselves — the blog works, it just has no pictures.

**Flux is a hard dependency.** The views use `<flux:*>` throughout.

## Installation

```bash
composer require kreetancraft/laravel-blog
```
```bash
php artisan migrate
```

### Let Tailwind see this package

Required. Tailwind v4 generates only the classes it finds by scanning files, and it does not scan
`vendor/`. In `resources/css/app.css`:

```css
@source '../../vendor/kreetancraft/laravel-blog/resources/views';
@source '../../vendor/kreetancraft/laravel-seo/resources/views';
```

Skipping it fails confusingly rather than loudly — classes shared with your own views still work
and only the ones unique to these packages go missing.

## The editor

A TipTap editor ships with the package, bundled — **no npm packages, no build step, nothing to
publish**. It is served from the package itself, so upgrading is `composer update` alone.

```blade
<x-blog::rich-text model="content" :label="__('Body')" :rows="20" />
```

What it does: bold, italic, underline, strikethrough, three heading levels, bullet, numbered and
**task lists**, blockquote, code block, links, images, text alignment, colour and highlight,
horizontal rule, clear formatting, undo/redo, source view and fullscreen — plus:

| | |
|---|---|
| **Tables** | Insert, resizable columns, add/remove rows and columns, header row, merge and split cells |
| **Callouts** | Note, tip, warning and danger, as `<div data-callout="…">` so a sanitiser leaves them alone |
| **YouTube embeds** | Paste a URL; served cookie-free |
| **Find and replace** | Across the whole document, with match case |
| **Word and character count** | Live, under the editor |
| **Slash menu** | Type `/` at the start of a block for a filtered command palette — headings, lists, table, image, video, callouts, divider. Arrow keys and Enter, or click |
| **Paste cleanup** | Word and Google Docs paste a document, not a fragment — `mso-*` styles, `<o:p>` tags and a class on every element. Structure is kept, presentation dropped |

Images come from your own picker via `blog.media_picker_view`; the editor dispatches
`media-picked` for the `rich-text-image` group and inserts what it receives.

To replace the editor entirely, publish the views and edit
`resources/views/vendor/blog/components/rich-text.blade.php`, or set
`blog.routes.serve_assets` to false and load your own.

## Permissions

Every policy declares a subject, so with
[kreetancraft/laravel-user-management](https://github.com/Kreetancraft/laravel-user-management)
installed one command creates all of them:

```bash
php artisan user-management:sync-permissions
```

| Subject | Abilities |
|---|---|
| `post` | view, create, update, delete, **publish** |
| `blog-comment` | view, create, update, delete, **moderate** |
| `blog-category` | view, create, update, delete |
| `blog-tag` | view, create, update, delete |
| `blog-author` | view, create, update, delete |
| `blog-series` | view, create, update, delete |

Publishing is separate from editing on purpose: a writer may draft without being able to put it
in front of readers. The taxonomy subjects are prefixed `blog-` because `category` and `tag` are
words other packages will want too.

**Posts** and **Comments** links also appear in that package's sidebar, with nothing declared on
either side.

## SEO

`kreetancraft/laravel-seo` is a hard dependency — posts, categories, authors and series all carry
meta, JSON-LD and sitemap entries. This package contributes itself to that one through container
tags, so neither names the other in config:

- its four SEO-enabled models appear on the bulk SEO screen
- its URLs appear in `GET /api/v1/sitemap`
- `BlogJsonLd::posting()` builds `BlogPosting` schema from the site-wide publisher node

Front-end paths default to `/blog/{slug}`, `/blog/category/{slug}` and so on. Override any of
them in `config/seo.php` under `paths`.

## Images

```php
// config/blog.php
'image_resolver' => \Kreetancraft\Media\Support\MediaImageResolver::class,
'media_picker_view' => 'components.my-media-picker',
```

The resolver turns a model plus a collection name into URLs; the picker view is yours, rendered
where a picker belongs and handed `$items` (already resolved) plus `$group`.

Listings call `Post::preloadImages()` once per page, so images cost one query for the page rather
than two per row.

## The public API

Read-only, and only published posts:

```
GET  /api/v1/posts                      index, filterable by category, tag, author, series, featured
GET  /api/v1/posts/{slug}               detail, with JSON-LD, neighbours, related and approved comments
GET  /api/v1/categories                 GET /api/v1/categories/{slug}
GET  /api/v1/authors/{slug}             GET /api/v1/series/{slug}
GET  /api/v1/tags
POST /api/v1/posts/{slug}/comments      always lands in the moderation queue as pending
```

Rate limiters are configurable and unset by default — naming one your app has not defined would
throw on routes this package registers automatically:

```php
'api_rate_limiter' => 'api-read',
'api_write_rate_limiter' => 'api-sensitive',
```

Set `blog.comments.enabled` to false and the comment endpoint is not registered at all.

## Scheduled publishing

```bash
php artisan blogs:publish-scheduled
```

Schedule it in `routes/console.php`; posts with a `scheduled` status and a due `published_at`
become published and the API cache is flushed.

## Requirements

PHP 8.2+, Laravel 12 or 13, Livewire 4, Flux 2, and `kreetancraft/laravel-seo`.

## License

MIT.
