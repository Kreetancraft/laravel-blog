<?php

namespace Kreetancraft\Blog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Kreetancraft\Blog\Models\Category;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => rtrim(fake()->unique()->words(2, true), '.'),
            'description' => fake()->sentence(),
        ];
    }
}
