<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Abecedario', 'description' => 'Las 27 letras del abecedario en LESCO.',          'icon' => '🔤', 'order' => 1],
            ['name' => 'Números',    'description' => 'Del 0 al 20 en Lengua de Señas Costarricense.',  'icon' => '🔢', 'order' => 2],
            ['name' => 'Saludos',    'description' => 'Expresiones básicas de saludo y despedida.',      'icon' => '👋', 'order' => 3],
            ['name' => 'Familia',    'description' => 'Señas para los miembros del núcleo familiar.',   'icon' => '👨‍👩‍👧', 'order' => 4],
            ['name' => 'Colores',    'description' => 'Los colores principales y sus señas.',            'icon' => '🎨', 'order' => 5],
            ['name' => 'Animales',   'description' => 'Animales domésticos y de granja en LESCO.',      'icon' => '🐾', 'order' => 6],
            ['name' => 'Emociones',  'description' => 'Cómo expresar sentimientos y emociones.',        'icon' => '😊', 'order' => 7],
            ['name' => 'Alimentos',  'description' => 'Frutas, verduras y comidas del día a día.',      'icon' => '🍎', 'order' => 8],
        ];

        foreach ($categories as $data) {
            Category::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
