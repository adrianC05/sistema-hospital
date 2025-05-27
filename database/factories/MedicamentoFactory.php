<?php

namespace Database\Factories;

use App\Models\Medicamento;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicamentoFactory extends Factory
{
    protected $model = Medicamento::class;

    public function definition(): array
    {
        return [
            'codigo' => 'MED-' . $this->faker->unique()->numberBetween(100, 999),
            'nombre' => $this->faker->word() . ' ' . $this->faker->word(),
            'precio' => $this->faker->randomFloat(2, 5, 150),
            'dosis' => $this->faker->randomElement(['1 cada 8 horas', '2 al día', '1 por la noche']),
            'detalles' => $this->faker->sentence(),
            'stock' => $this->faker->numberBetween(50, 1000),
        ];
    }
}