<?php

namespace Database\Factories;

use App\Enums\GameType;
use App\Models\Game;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(GameType::cases());

        return [
            'lesson_id' => Lesson::factory()->published(),
            'type'      => $type,
            'config'    => $this->configFor($type),
        ];
    }

    public function memoria(): static
    {
        return $this->state(fn () => [
            'type'   => GameType::Memoria,
            'config' => $this->configFor(GameType::Memoria),
        ]);
    }

    public function seleccionMultiple(): static
    {
        return $this->state(fn () => [
            'type'   => GameType::SeleccionMultiple,
            'config' => $this->configFor(GameType::SeleccionMultiple),
        ]);
    }

    public function asociacion(): static
    {
        return $this->state(fn () => [
            'type'   => GameType::Asociacion,
            'config' => $this->configFor(GameType::Asociacion),
        ]);
    }

    /** @return array<string, mixed> */
    private function configFor(GameType $type): array
    {
        return match ($type) {
            GameType::Memoria => [
                'pairs' => collect(range(1, 4))->map(fn ($i) => [
                    'id'    => $i,
                    'word'  => fake()->word(),
                    'image' => 'https://picsum.photos/seed/' . fake()->word() . '/120/120',
                ])->all(),
            ],
            GameType::SeleccionMultiple => [
                'questions' => collect(range(1, 5))->map(fn ($i) => [
                    'id'            => $i,
                    'question'      => '¿Cuál es la seña de "' . fake()->word() . '"?',
                    'image_url'     => 'https://picsum.photos/seed/' . fake()->word() . '/300/200',
                    'options'       => collect(['A', 'B', 'C', 'D'])->map(fn ($l) => fake()->word())->values()->all(),
                    'correct_index' => fake()->numberBetween(0, 3),
                ])->all(),
            ],
            GameType::Asociacion => [
                'pairs' => collect(range(1, 4))->map(fn ($i) => [
                    'id'        => $i,
                    'left'      => fake()->word(),
                    'right'     => 'https://picsum.photos/seed/' . fake()->word() . '/120/120',
                    'left_type'  => 'text',
                    'right_type' => 'image',
                ])->all(),
            ],
            GameType::ReconocimientoVisual => [
                'rounds' => collect(range(1, 5))->map(fn ($i) => [
                    'id'            => $i,
                    'image_url'     => 'https://picsum.photos/seed/' . fake()->word() . '/400/300',
                    'options'       => collect(range(0, 3))->map(fn () => fake()->word())->all(),
                    'correct_index' => fake()->numberBetween(0, 3),
                ])->all(),
            ],
        };
    }
}
