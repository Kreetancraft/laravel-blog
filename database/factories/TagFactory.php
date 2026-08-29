<?php

namespace Kreetancraft\Blog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Kreetancraft\Blog\Models\Tag;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->words(2, true)),
        ];
    }
}
