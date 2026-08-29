<?php

namespace Kreetancraft\Blog\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

/**
 * Stands in for kreetancraft/laravel-media-manager's MediaImageResolver.
 *
 * The seam is duck-typed on purpose — a host can point `blog.image_resolver` at
 * its own class without implementing an interface from a package it does not
 * depend on — so this asserts the shape by being that shape, with no media
 * package installed.
 */
class StubImageResolver
{
    /** @var array<string, list<array{id: int, url: string, name: string}>> */
    public static array $images = [];

    /** @var list<string> */
    public static array $preloaded = [];

    public static function reset(): void
    {
        self::$images = [];
        self::$preloaded = [];
    }

    public static function give(Model $model, string $collection, string $url): void
    {
        self::$images[self::key($model, $collection)] = [
            ['id' => 1, 'url' => $url, 'name' => 'stub.jpg'],
        ];
    }

    public function urlFor(Model $model, string $collection): ?string
    {
        return $this->listFor($model, $collection)[0]['url'] ?? null;
    }

    /**
     * @return list<array{id: int, url: string, name: string}>
     */
    public function listFor(Model $model, string $collection): array
    {
        return self::$images[self::key($model, $collection)] ?? [];
    }

    /**
     * @param  list<int|string>  $ids
     */
    public function syncFor(Model $model, string $collection, array $ids): void
    {
        self::$images[self::key($model, $collection)] = array_map(
            fn ($id) => ['id' => (int) $id, 'url' => '/stub/'.$id.'.jpg', 'name' => 'stub.jpg'],
            array_values($ids),
        );
    }

    /**
     * @param  iterable<Model>  $models
     */
    public function preload(iterable $models, string $collection): void
    {
        foreach ($models as $model) {
            self::$preloaded[] = self::key($model, $collection);
        }
    }

    private static function key(Model $model, string $collection): string
    {
        return $model->getMorphClass().':'.$model->getKey().':'.$collection;
    }
}
