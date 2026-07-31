<?php

namespace Database\Factories;

use App\Models\ChildProfile;
use App\Models\Game;
use App\Models\GameAttempt;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GameAttempt>
 */
class GameAttemptFactory extends Factory
{
    public function definition(): array
    {
        $correct = fake()->numberBetween(0, 10);
        $wrong   = fake()->numberBetween(0, 5);

        return [
            'child_profile_id' => ChildProfile::factory(),
            'game_id'          => Game::factory(),
            'correct_answers'  => $correct,
            'wrong_answers'    => $wrong,
            'score'            => $correct * 10,
            'duration_seconds' => fake()->numberBetween(20, 300),
            'idempotency_key'  => Str::uuid()->toString(),
            'completed_at'     => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function perfect(): static
    {
        return $this->state(fn () => [
            'correct_answers' => 10,
            'wrong_answers'   => 0,
            'score'           => 100,
        ]);
    }

    public function passing(): static
    {
        return $this->state(fn () => [
            'correct_answers' => fake()->numberBetween(7, 10),
            'wrong_answers'   => fake()->numberBetween(0, 3),
            'score'           => fake()->numberBetween(70, 100),
        ]);
    }

    public function failing(): static
    {
        return $this->state(fn () => [
            'correct_answers' => fake()->numberBetween(0, 6),
            'wrong_answers'   => fake()->numberBetween(4, 10),
            'score'           => fake()->numberBetween(0, 65),
        ]);
    }
}
