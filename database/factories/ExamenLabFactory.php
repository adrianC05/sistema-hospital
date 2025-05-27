<?php

namespace Database\Factories;

use App\Models\ExamenLab;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExamenLabFactory extends Factory
{
    protected $model = ExamenLab::class;

    public function definition(): array
    {
        return [
            'codigo' => 'EX-' . $this->faker->unique()->numberBetween(100, 999),
            'nombre_examen' => $this->faker->words(3, true) . ' Test',
            'precio' => $this->faker->randomFloat(2, 25, 300),
            'resultado' => $this->faker->sentence(),
        ];
    }
}