<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\LessonResource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonResource>
 */
class LessonResourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lesson_id'   => Lesson::factory()->published(),
            'type'        => 'video',
            'url'         => 'https://www.youtube.com/embed/' . fake()->lexify('???????????'),
            'description' => fake()->optional(0.5)->sentence(),
        ];
    }

    public function video(): static
    {
        return $this->state([
            'type' => 'video',
            'url'  => 'https://www.youtube.com/embed/' . fake()->lexify('???????????'),
        ]);
    }

    public function image(): static
    {
        return $this->state([
            'type' => 'imagen',
            'url'  => 'https://picsum.photos/seed/' . fake()->word() . '/400/300',
        ]);
    }
}
