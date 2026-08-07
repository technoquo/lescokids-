<?php

namespace Database\Seeders;

use App\Enums\GameType;
use App\Enums\LessonStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Game;
use App\Models\Lesson;
use App\Models\LessonResource;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first()
            ?? User::firstOrCreate(
                ['email' => 'admin@lescokids.cr'],
                [
                    'name' => 'Admin LescoKids',
                    'password' => Hash::make('password'),
                    'role' => UserRole::Admin,
                    'email_verified_at' => now(),
                ]
            );

        $lessons = [
            'Abecedario' => [
                ['title' => 'Letras A, B, C',     'order' => 1, 'difficulty_level' => 'basico'],
                ['title' => 'Letras D, E, F',     'order' => 2, 'difficulty_level' => 'basico'],
                ['title' => 'Letras G, H, I',     'order' => 3, 'difficulty_level' => 'basico'],
                ['title' => 'Letras J, K, L',     'order' => 4, 'difficulty_level' => 'intermedio'],
                ['title' => 'Letras M, N, Ñ',     'order' => 5, 'difficulty_level' => 'intermedio'],
            ],
            'Números' => [
                ['title' => 'Números 0 al 5',     'order' => 1, 'difficulty_level' => 'basico'],
                ['title' => 'Números 6 al 10',    'order' => 2, 'difficulty_level' => 'basico'],
                ['title' => 'Números 11 al 20',   'order' => 3, 'difficulty_level' => 'intermedio'],
            ],
            'Saludos' => [
                ['title' => 'Hola y Adiós',       'order' => 1, 'difficulty_level' => 'basico'],
                ['title' => 'Buenos días/tardes',  'order' => 2, 'difficulty_level' => 'basico'],
                ['title' => '¿Cómo estás?',       'order' => 3, 'difficulty_level' => 'basico'],
                ['title' => 'Por favor y Gracias', 'order' => 4, 'difficulty_level' => 'basico'],
            ],
            'Familia' => [
                ['title' => 'Papá y Mamá',        'order' => 1, 'difficulty_level' => 'basico'],
                ['title' => 'Hermanos',            'order' => 2, 'difficulty_level' => 'basico'],
                ['title' => 'Abuelos',             'order' => 3, 'difficulty_level' => 'intermedio'],
                ['title' => 'Tíos y primos',       'order' => 4, 'difficulty_level' => 'intermedio'],
            ],
            'Colores' => [
                ['title' => 'Rojo, Azul, Amarillo', 'order' => 1, 'difficulty_level' => 'basico'],
                ['title' => 'Verde, Naranja, Morado', 'order' => 2, 'difficulty_level' => 'basico'],
                ['title' => 'Blanco y Negro',       'order' => 3, 'difficulty_level' => 'basico'],
            ],
            'Animales' => [
                ['title' => 'Perro y Gato',        'order' => 1, 'difficulty_level' => 'basico'],
                ['title' => 'Vaca y Caballo',       'order' => 2, 'difficulty_level' => 'basico'],
                ['title' => 'Pájaro y Pez',         'order' => 3, 'difficulty_level' => 'intermedio'],
            ],
            'Emociones' => [
                ['title' => 'Feliz y Triste',      'order' => 1, 'difficulty_level' => 'basico'],
                ['title' => 'Enojado y Asustado',  'order' => 2, 'difficulty_level' => 'basico'],
                ['title' => 'Sorprendido y Cansado', 'order' => 3, 'difficulty_level' => 'intermedio'],
            ],
            'Alimentos' => [
                ['title' => 'Frutas básicas',      'order' => 1, 'difficulty_level' => 'basico'],
                ['title' => 'Verduras',            'order' => 2, 'difficulty_level' => 'basico'],
                ['title' => 'Desayuno',            'order' => 3, 'difficulty_level' => 'intermedio'],
            ],
        ];

        foreach ($lessons as $categoryName => $categoryLessons) {
            $category = Category::where('name', $categoryName)->first();

            if (! $category) {
                continue;
            }

            foreach ($categoryLessons as $data) {
                $lesson = Lesson::firstOrCreate(
                    ['category_id' => $category->id, 'order' => $data['order']],
                    [
                        'title' => $data['title'],
                        'description' => "Aprende a hacer la seña de {$data['title']} en LESCO.",
                        'difficulty_level' => $data['difficulty_level'],
                        'status' => LessonStatus::Publicado,
                        'created_by' => $admin->id,
                        'approved_by' => $admin->id,
                    ]
                );

                // Add one resource if missing
                if ($lesson->resources()->doesntExist()) {
                    LessonResource::create([
                        'lesson_id' => $lesson->id,
                        'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
                        'image_url' => null,
                        'description' => "Video de la seña: {$lesson->title}",
                    ]);
                }

                // Add one game if missing
                if ($lesson->games()->doesntExist()) {
                    Game::create([
                        'lesson_id' => $lesson->id,
                        'type' => GameType::SeleccionMultiple,
                        'config' => [
                            'questions' => array_map(fn ($i) => [
                                'id' => $i,
                                'question' => "Pregunta {$i} sobre \"{$lesson->title}\"",
                                'image_url' => null,
                                'options' => ['Seña 1', 'Seña 2', 'Seña 3', 'Seña 4'],
                                'correct_index' => 0,
                            ], range(1, 5)),
                        ],
                    ]);
                }
            }
        }
    }
}
