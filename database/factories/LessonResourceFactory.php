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
            'lesson_id' => Lesson::factory()->published(),
            'video_url' => 'https://www.youtube.com/embed/'.fake()->lexify('???????????'),
            'image_url' => null,
            'description' => fake()->optional(0.5)->sentence(),
        ];
    }

    public function video(): static
    {
        return $this->state([
            'video_url' => 'https://www.youtube.com/embed/'.fake()->lexify('???????????'),
            'image_url' => null,
        ]);
    }

    public function image(): static
    {
        return $this->state([
            'video_url' => null,
            'image_url' => 'https://picsum.photos/seed/'.fake()->word().'/400/300',
        ]);
    }

    public function both(): static
    {
        return $this->state([
            'video_url' => 'https://www.youtube.com/embed/'.fake()->lexify('???????????'),
            'image_url' => 'https://picsum.photos/seed/'.fake()->word().'/400/300',
        ]);
    }
}
