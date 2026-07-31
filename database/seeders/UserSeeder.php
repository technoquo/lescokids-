<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\ChildProfile;
use App\Models\Classroom;
use App\Models\GameAttempt;
use App\Models\Progress;
use App\Models\User;
use App\Services\ProgressService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(ProgressService $progressService): void
    {
        // ── Admin ──────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@lescokids.cr'],
            [
                'name'              => 'Admin LescoKids',
                'password'          => Hash::make('password'),
                'role'              => UserRole::Admin,
                'email_verified_at' => now(),
            ]
        );

        // ── Tutor demo ─────────────────────────────────────────
        $tutor = User::firstOrCreate(
            ['email' => 'tutor@lescokids.cr'],
            [
                'name'              => 'María Rodríguez',
                'password'          => Hash::make('password'),
                'role'              => UserRole::Tutor,
                'email_verified_at' => now(),
            ]
        );

        // Two demo children for the tutor
        $sofia = ChildProfile::firstOrCreate(
            ['guardian_id' => $tutor->id, 'name' => 'Sofía'],
            [
                'avatar'     => '🐱',
                'birth_date' => '2018-03-15',
                'login_pin'  => '1234',
            ]
        );

        $marcos = ChildProfile::firstOrCreate(
            ['guardian_id' => $tutor->id, 'name' => 'Marcos'],
            [
                'avatar'     => '🐶',
                'birth_date' => '2016-07-22',
                'login_pin'  => '5678',
            ]
        );

        foreach ([$sofia, $marcos] as $child) {
            $child->guardians()->syncWithoutDetaching([$tutor->id => ['relationship_type' => 'tutor']]);
            $progressService->seedInitialProgress($child);
        }

        // Give Sofía some completed progress
        Progress::where('child_profile_id', $sofia->id)
            ->limit(3)
            ->get()
            ->each(function ($progress) use ($sofia) {
                $progress->update([
                    'status'       => 'completado',
                    'best_score'   => rand(75, 100),
                    'completed_at' => Carbon::now()->subDays(rand(1, 10)),
                ]);

                // Simulate a game attempt for each completion
                if ($progress->lesson->games()->exists()) {
                    $game = $progress->lesson->games()->first();
                    GameAttempt::firstOrCreate(
                        ['child_profile_id' => $sofia->id, 'game_id' => $game->id],
                        [
                            'score'            => $progress->best_score,
                            'correct_answers'  => 8,
                            'wrong_answers'    => 2,
                            'duration_seconds' => rand(30, 120),
                            'idempotency_key'  => \Illuminate\Support\Str::uuid(),
                            'completed_at'     => $progress->completed_at,
                        ]
                    );
                }
            });

        // ── Docente demo ───────────────────────────────────────
        $docente = User::firstOrCreate(
            ['email' => 'docente@lescokids.cr'],
            [
                'name'              => 'Carlos Mora',
                'password'          => Hash::make('password'),
                'role'              => UserRole::Docente,
                'email_verified_at' => now(),
            ]
        );

        $classroom = Classroom::firstOrCreate(
            ['docente_id' => $docente->id, 'name' => 'Aula Arco Iris'],
            ['description' => 'Grupo de primaria nivel inicial.']
        );

        // Enroll Sofía in the classroom
        $classroom->students()->syncWithoutDetaching([$sofia->id]);
        $sofia->guardians()->syncWithoutDetaching([$docente->id => ['relationship_type' => 'docente']]);

        // Extra tutors with children (for richer test data)
        User::factory()->tutor()->count(3)->create()->each(function (User $extraTutor) use ($progressService) {
            $children = ChildProfile::factory()->forGuardian($extraTutor)->count(rand(1, 3))->create();
            foreach ($children as $child) {
                $child->guardians()->syncWithoutDetaching([$extraTutor->id => ['relationship_type' => 'tutor']]);
                $progressService->seedInitialProgress($child);
            }
        });

        $this->command->info('  Usuarios creados:');
        $this->command->info('  admin@lescokids.cr   / password  (Admin)');
        $this->command->info('  tutor@lescokids.cr   / password  (Tutor — niños: Sofía 1234, Marcos 5678)');
        $this->command->info('  docente@lescokids.cr / password  (Docente — Aula Arco Iris)');
    }
}
