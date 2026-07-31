<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class, // 1. Categorías LESCO (sin dependencias)
            LessonSeeder::class,   // 2. Lecciones + recursos + juegos (depende de categories + admin user)
            BadgeSeeder::class,    // 3. Medallas (depende de categories)
            UserSeeder::class,     // 4. Usuarios demo + niños + progreso (depende de todo lo anterior)
        ]);
    }
}
