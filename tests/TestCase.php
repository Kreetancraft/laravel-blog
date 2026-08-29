<?php

namespace Kreetancraft\Blog\Tests;

use Flux\FluxServiceProvider;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Kreetancraft\Blog\Providers\BlogServiceProvider;
use Kreetancraft\Blog\Tests\Fixtures\Models\User;
use Kreetancraft\Seo\Providers\SeoServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\Permission\PermissionServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            FluxServiceProvider::class,
            PermissionServiceProvider::class,
            SeoServiceProvider::class,
            BlogServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            // SQLite ignores foreign keys unless asked. Without this the
            // cascade deletes between posts, comments and taxonomies are never
            // exercised.
            'foreign_key_constraints' => true,
        ]);
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        // This package resolves the user model from auth config and never
        // imports a concrete class, so the suite points it at the fixture.
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('permission.testing', true);
        $app['config']->set('seo.frontend_url', 'https://example.test');

        // tests/fixtures/views stands in for the host application: this package
        // ships no layout, so the suite provides the one its screens render into.
        $app['config']->set('view.paths', [
            __DIR__.'/fixtures/views',
            __DIR__.'/../resources/views',
            resource_path('views'),
        ]);
        $app['config']->set('blog.layouts.admin', 'fixtures-layout');
    }

    /**
     * A dashboard for the breadcrumbs to point at. The host owns this route;
     * without it Layout::home() falls back to the site root, which is correct
     * but makes the breadcrumb assertions meaningless.
     */
    protected function defineRoutes($router): void
    {
        $router->middleware('web')->group(function ($router) {
            $router->get('/dashboard', fn () => 'dashboard')->name('dashboard');
            $router->get('/login', fn () => 'login')->name('login');
        });
    }

    /**
     * Host-owned tables first (users, permissions), then this package's.
     *
     * loadLaravelMigrations() is deliberately not used: on SQLite in-memory it
     * left an open transaction that made every second test fail with "cannot
     * start a transaction within a transaction".
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/fixtures/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../vendor/kreetancraft/laravel-seo/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
