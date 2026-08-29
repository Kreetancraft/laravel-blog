<?php

namespace Kreetancraft\Blog\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Kreetancraft\Blog\Console\Commands\PublishScheduledPosts;
use Kreetancraft\Blog\Contracts\BlogsContract;
use Kreetancraft\Blog\Livewire\CreateAuthor;
use Kreetancraft\Blog\Livewire\CreatePost;
use Kreetancraft\Blog\Livewire\CreateSeries;
use Kreetancraft\Blog\Livewire\EditAuthor;
use Kreetancraft\Blog\Livewire\EditPost;
use Kreetancraft\Blog\Livewire\EditSeries;
use Kreetancraft\Blog\Livewire\ManageAuthors;
use Kreetancraft\Blog\Livewire\ManageCategories;
use Kreetancraft\Blog\Livewire\ManageComments;
use Kreetancraft\Blog\Livewire\ManagePosts;
use Kreetancraft\Blog\Livewire\ManageSeries;
use Kreetancraft\Blog\Livewire\ManageTags;
use Kreetancraft\Blog\Livewire\ViewComment;
use Kreetancraft\Blog\Models\Author;
use Kreetancraft\Blog\Models\Category;
use Kreetancraft\Blog\Models\Comment;
use Kreetancraft\Blog\Models\Post;
use Kreetancraft\Blog\Models\Series;
use Kreetancraft\Blog\Models\Tag;
use Kreetancraft\Blog\Policies\AuthorPolicy;
use Kreetancraft\Blog\Policies\CategoryPolicy;
use Kreetancraft\Blog\Policies\CommentPolicy;
use Kreetancraft\Blog\Policies\PostPolicy;
use Kreetancraft\Blog\Policies\SeriesPolicy;
use Kreetancraft\Blog\Policies\TagPolicy;
use Kreetancraft\Blog\Repositories\BlogsRepository;
use Kreetancraft\Blog\Seo\BlogSitemapProvider;
use Kreetancraft\Blog\Support\BlogApiCache;
use Kreetancraft\Seo\Support\SeoModelRegistry;
use Kreetancraft\Seo\Support\SitemapProviderRegistry;
use Livewire\Livewire;

class BlogServiceProvider extends ServiceProvider
{
    /**
     * Model => policy. Registered through Gate, which is also how
     * kreetancraft/laravel-user-management discovers them.
     *
     * @var array<class-string, class-string>
     */
    private const POLICIES = [
        Post::class => PostPolicy::class,
        Comment::class => CommentPolicy::class,
        Tag::class => TagPolicy::class,
        Category::class => CategoryPolicy::class,
        Author::class => AuthorPolicy::class,
        Series::class => SeriesPolicy::class,
    ];

    /**
     * Models whose writes invalidate the public API cache.
     *
     * @var array<class-string>
     */
    private const CACHED_MODELS = [Post::class, Category::class, Tag::class, Author::class, Series::class];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/blog.php', 'blog');

        $this->app->bind(BlogsContract::class, BlogsRepository::class);

        $this->registerNavigation();
        $this->registerSeo();
    }

    public function boot(): void
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerMigrations();
        $this->registerRoutes();
        $this->registerLivewire();

        foreach (self::POLICIES as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Bust the public API cache on any write, including the scheduled
        // publish command, which only updates a post status.
        foreach (self::CACHED_MODELS as $model) {
            $model::saved(fn () => BlogApiCache::flush());
            $model::deleted(fn () => BlogApiCache::flush());
        }

        if ($this->app->runningInConsole()) {
            $this->commands([PublishScheduledPosts::class]);
        }
    }

    /**
     * Sidebar links, contributed through a container tag.
     *
     * The tag is written out rather than referenced as Navigation::TAG, because
     * naming that class would be a dependency on
     * kreetancraft/laravel-user-management. Nothing reads the tag unless that
     * package is installed, and then the links appear on their own.
     */
    protected function registerNavigation(): void
    {
        $this->app->bind('blog.navigation.items', fn () => [
            [
                'label' => __('Posts'),
                'icon' => 'newspaper',
                'route' => config('blog.routes.names.posts', 'admin.blog.posts'),
                'ability' => 'viewAny',
                'model' => Post::class,
                'sort' => 50,
            ],
            [
                'label' => __('Comments'),
                'icon' => 'chat-bubble-left-right',
                'route' => config('blog.routes.names.comments', 'admin.blog.comments'),
                'ability' => 'viewAny',
                'model' => Comment::class,
                'sort' => 51,
            ],
        ]);

        $this->app->tag('blog.navigation.items', 'admin.navigation');
    }

    /**
     * What this package contributes to kreetancraft/laravel-seo: its four
     * SEO-enabled models, and the sitemap URLs for them.
     *
     * Contributed through that package's tags, so it needs no knowledge of
     * blogs and nothing has to be listed centrally.
     */
    protected function registerSeo(): void
    {
        // Where this package's content lives on the public site. The SEO
        // package ships paths for nothing, since it knows no content types;
        // these are the defaults for ours, and a host value wins over them.
        $this->app->booted(function (): void {
            config(['seo.paths' => array_merge([
                'blog_post' => '/blog/{slug}',
                'blog_category' => '/blog/category/{slug}',
                'blog_tag' => '/blog/tag/{slug}',
                'blog_author' => '/blog/author/{slug}',
                'blog_series' => '/blog/series/{slug}',
            ], (array) config('seo.paths', []))]);
        });

        $this->app->bind('blog.seo.models', fn () => [
            Post::class => [
                'label' => 'Blog post',
                'title' => 'title',
                'edit_route' => config('blog.routes.names.posts.edit', 'admin.blog.posts.edit'),
                'route_param' => true,
            ],
            Category::class => [
                'label' => 'Blog category',
                'title' => 'name',
                'edit_route' => config('blog.routes.names.categories', 'admin.blog.categories'),
                'route_param' => false,
            ],
            Author::class => [
                'label' => 'Blog author',
                'title' => 'name',
                'edit_route' => config('blog.routes.names.authors.edit', 'admin.blog.authors.edit'),
                'route_param' => true,
            ],
            Series::class => [
                'label' => 'Blog series',
                'title' => 'title',
                'edit_route' => config('blog.routes.names.series.edit', 'admin.blog.series.edit'),
                'route_param' => true,
            ],
        ]);

        $this->app->tag('blog.seo.models', SeoModelRegistry::TAG);

        $this->app->bind('blog.sitemap', fn () => BlogSitemapProvider::class);
        $this->app->tag('blog.sitemap', SitemapProviderRegistry::TAG);
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            __DIR__.'/../../config/blog.php' => config_path('blog.php'),
        ], 'blog-config');
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'blog');

        // The generic form and editor components these screens use. They lived
        // in the host application before; shipping them is what makes the views
        // render anywhere.
        Blade::anonymousComponentPath(__DIR__.'/../../resources/views/components', 'blog');

        $this->publishes([
            __DIR__.'/../../resources/views' => resource_path('views/vendor/blog'),
        ], 'blog-views');
    }

    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $this->publishesMigrations([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'blog-migrations');
    }

    protected function registerLivewire(): void
    {
        $components = [
            'blog.posts' => ManagePosts::class,
            'blog.posts.create' => CreatePost::class,
            'blog.posts.edit' => EditPost::class,
            'blog.categories' => ManageCategories::class,
            'blog.tags' => ManageTags::class,
            'blog.authors' => ManageAuthors::class,
            'blog.authors.create' => CreateAuthor::class,
            'blog.authors.edit' => EditAuthor::class,
            'blog.series' => ManageSeries::class,
            'blog.series.create' => CreateSeries::class,
            'blog.series.edit' => EditSeries::class,
            'blog.comments' => ManageComments::class,
            'blog.comments.show' => ViewComment::class,
        ];

        foreach ($components as $alias => $class) {
            Livewire::component($alias, $class);
        }
    }

    /**
     * Two independent route groups: the admin screens and the public read API.
     * A host may serve the API while replacing the admin UI, or the reverse.
     */
    protected function registerRoutes(): void
    {
        if (config('blog.routes.register', true)) {
            Route::group([
                'prefix' => config('blog.routes.prefix', 'admin'),
                'middleware' => config('blog.routes.middleware', ['web', 'auth']),
            ], function (): void {
                $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
            });
        }

        if (config('blog.routes.register_api', true)) {
            Route::group([
                'prefix' => config('blog.routes.api_prefix', 'api'),
                'middleware' => config('blog.routes.api_middleware', ['api']),
            ], function (): void {
                $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
            });
        }
    }
}
