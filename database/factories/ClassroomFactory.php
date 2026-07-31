<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'docente_id'  => User::factory()->docente(),
            'name'        => 'Aula ' . fake()->randomElement(['Arco Iris', 'Girasol', 'Mariposa', 'Estrella', 'Luna', 'Sol']),
            'description' => fake()->optional(0.6)->sentence(),
        ];
    }

    public function forDocente(User $docente): static
    {
        return $this->state(['docente_id' => $docente->id]);
    }
}
