<?php

namespace Database\Seeders;

use App\Models\ExamenLab;
use App\Models\ImgRayosX;
use App\Models\Medicamento;
use App\Models\Procedimiento;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test',
            'last_name' => 'User',
            'ci' => '12345678',
            'email' => 'admin@gmail.com',
            'password' => 'admin',

        ]);
        // Crear 10 usuarios
        User::factory()->count(10)->create();

        // Crear 10 exámenes de laboratorio
        ExamenLab::factory()->count(10)->create();

        // Crear 5 imágenes de Rayos X
        ImgRayosX::factory()->count(10)->create();

        // Crear 8 procedimientos
        Procedimiento::factory()->count(10)->create();

        // Crear 20 medicamentos
        Medicamento::factory()->count(20)->create();
    }
}
