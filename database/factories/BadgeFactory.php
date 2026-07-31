<?php

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Badge>
 */
class BadgeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'        => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'icon'        => fake()->randomElement(['🏅', '⭐', '🌟', '🎖️', '🏆', '🥇', '💎', '🌈']),
            'criteria'    => ['lessons_completed' => fake()->numberBetween(1, 20)],
        ];
    }

    public function forLessonsCompleted(int $count): static
    {
        return $this->state(['criteria' => ['lessons_completed' => $count]]);
    }

    public function forCategoryCompleted(int $categoryId): static
    {
        return $this->state(['criteria' => ['category_completed' => $categoryId]]);
    }

    public function forPerfectScore(): static
    {
        return $this->state(['criteria' => ['perfect_score' => true]]);
    }
}
