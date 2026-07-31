<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    private static int $order = 0;

    public function definition(): array
    {
        return [
            'name'        => fake()->unique()->randomElement(['Abecedario', 'Números', 'Familia', 'Colores', 'Animales', 'Saludos', 'Emociones', 'Alimentos']),
            'description' => fake()->sentence(),
            'order'       => ++self::$order,
            'icon'        => fake()->randomElement(['🔤', '🔢', '👨‍👩‍👧', '🎨', '🐾', '👋', '😊', '🍎']),
        ];
    }
}
