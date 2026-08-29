<?php

namespace Kreetancraft\Blog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Kreetancraft\Blog\Enums\PostStatus;
use Kreetancraft\Blog\Models\Author;
use Kreetancraft\Blog\Models\Post;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => rtrim(fake()->unique()->sentence(6), '.'),
            'excerpt' => fake()->paragraph(),
            'content' => '<p>'.implode('</p><p>', fake()->paragraphs(5)).'</p>',
            'status' => PostStatus::Draft,
            'published_at' => null,
            'author_id' => Author::factory(),
            'series_id' => null,
            'order_in_series' => null,
            'is_featured' => false,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => PostStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'status' => PostStatus::Scheduled,
            'published_at' => now()->addWeek(),
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => PostStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => PostStatus::Archived,
            'published_at' => now()->subMonth(),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (): array => ['is_featured' => true]);
    }
}
