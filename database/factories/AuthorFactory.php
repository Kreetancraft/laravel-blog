<?php

namespace Kreetancraft\Blog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Kreetancraft\Blog\Models\Author;

/**
 * @extends Factory<Author>
 */
class AuthorFactory extends Factory
{
    protected $model = Author::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->name(),
            'bio' => fake()->paragraph(),
        ];
    }
}
