<?php

namespace Kreetancraft\Blog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Kreetancraft\Blog\Enums\CommentStatus;
use Kreetancraft\Blog\Models\Comment;
use Kreetancraft\Blog\Models\Post;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'parent_id' => null,
            'user_id' => null,
            'author_name' => fake()->name(),
            'author_email' => fake()->safeEmail(),
            'author_url' => null,
            'content' => fake()->paragraph(),
            'status' => CommentStatus::Pending,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'referrer' => fake()->optional(0.7)->url(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => ['status' => CommentStatus::Pending]);
    }

    public function approved(): static
    {
        return $this->state(fn (): array => ['status' => CommentStatus::Approved]);
    }

    public function spam(): static
    {
        return $this->state(fn (): array => ['status' => CommentStatus::Spam]);
    }

    public function rejected(): static
    {
        return $this->state(fn (): array => ['status' => CommentStatus::Rejected]);
    }

    public function bot(): static
    {
        return $this->state(fn (): array => ['user_agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)']);
    }
}
