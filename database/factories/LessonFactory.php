<?php

namespace Database\Factories;

use App\Enums\LessonStatus;
use App\Models\Category;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id'      => Category::factory(),
            'title'            => fake()->words(3, true),
            'description'      => fake()->optional(0.7)->sentence(),
            'difficulty_level' => fake()->randomElement(['basico', 'intermedio', 'avanzado']),
            'order'            => fake()->unique()->numberBetween(1, 50),
            'status'           => LessonStatus::Borrador,
            'created_by'       => User::factory()->admin(),
            'approved_by'      => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status'      => LessonStatus::Publicado,
            'approved_by' => User::factory()->admin(),
        ]);
    }

    public function inReview(): static
    {
        return $this->state(['status' => LessonStatus::Revision]);
    }

    public function basic(): static
    {
        return $this->state(['difficulty_level' => 'basico']);
    }

    public function forCategory(Category $category, int $order): static
    {
        return $this->state(['category_id' => $category->id, 'order' => $order]);
    }
}
