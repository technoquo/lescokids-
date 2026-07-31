<?php

namespace Database\Factories;

use App\Models\ChildProfile;
use App\Models\Lesson;
use App\Models\Progress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Progress>
 */
class ProgressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'child_profile_id' => ChildProfile::factory(),
            'lesson_id'        => Lesson::factory()->published(),
            'status'           => 'no_iniciado',
            'best_score'       => 0,
            'unlocked_at'      => now(),
            'completed_at'     => null,
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status'       => 'completado',
            'best_score'   => fake()->numberBetween(70, 100),
            'completed_at' => fake()->dateTimeBetween('-60 days', 'now'),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state([
            'status'     => 'en_progreso',
            'best_score' => fake()->numberBetween(20, 65),
        ]);
    }
}
