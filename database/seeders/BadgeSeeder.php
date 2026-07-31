<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'name'        => 'Primera Seña',
                'description' => 'Completaste tu primera lección de LESCO.',
                'icon'        => '🌟',
                'criteria'    => ['lessons_completed' => 1],
            ],
            [
                'name'        => 'Aprendiz LESCO',
                'description' => 'Completaste 5 lecciones.',
                'icon'        => '📚',
                'criteria'    => ['lessons_completed' => 5],
            ],
            [
                'name'        => 'Estudiante Dedicado',
                'description' => 'Completaste 10 lecciones.',
                'icon'        => '🎓',
                'criteria'    => ['lessons_completed' => 10],
            ],
            [
                'name'        => 'Maestro LESCO',
                'description' => 'Completaste 20 lecciones.',
                'icon'        => '🏆',
                'criteria'    => ['lessons_completed' => 20],
            ],
            [
                'name'        => 'Respuesta Perfecta',
                'description' => 'Obtuviste 100% de aciertos en un juego.',
                'icon'        => '💎',
                'criteria'    => ['perfect_score' => true],
            ],
        ];

        // Category-completion badges
        foreach (Category::all() as $category) {
            $badges[] = [
                'name'        => "Experto en {$category->name}",
                'description' => "Completaste todas las lecciones de {$category->name}.",
                'icon'        => $category->icon ?? '🏅',
                'criteria'    => ['category_completed' => $category->id],
            ];
        }

        foreach ($badges as $data) {
            Badge::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
