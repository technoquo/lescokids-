<?php

namespace Database\Factories;

use App\Models\ChildProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChildProfile>
 */
class ChildProfileFactory extends Factory
{
    private const AVATARS = ['🐶', '🐱', '🐸', '🦊', '🐻', '🐼', '🐨', '🦁', '🐯', '🐮', '🐷', '🐙'];

    public function definition(): array
    {
        return [
            'guardian_id' => User::factory()->tutor(),
            'name'        => fake()->firstName(),
            'avatar'      => fake()->randomElement(self::AVATARS),
            'birth_date'  => fake()->dateTimeBetween('-12 years', '-4 years')->format('Y-m-d'),
            'login_pin'   => (string) fake()->numberBetween(1000, 9999),
        ];
    }

    public function forGuardian(User $guardian): static
    {
        return $this->state(['guardian_id' => $guardian->id]);
    }
}
