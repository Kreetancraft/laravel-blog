<?php

namespace Kreetancraft\Blog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Kreetancraft\Blog\Enums\SeriesStatus;
use Kreetancraft\Blog\Models\Series;

/**
 * @extends Factory<Series>
 */
class SeriesFactory extends Factory
{
    protected $model = Series::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => rtrim(fake()->unique()->sentence(3), '.'),
            'description' => fake()->paragraph(),
            'status' => SeriesStatus::Active,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => ['status' => SeriesStatus::Draft]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => ['status' => SeriesStatus::Archived]);
    }
}
