<?php

namespace Database\Factories;

use App\Models\Procedimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProcedimientoFactory extends Factory
{
    protected $model = Procedimiento::class;

    public function definition(): array
    {
        return [
            'codigo' => 'PROC-' . $this->faker->unique()->numberBetween(100, 999),
            'nombre' => $this->faker->randomElement(['Sutura Simple', 'Endoscopia', 'Biopsia de Piel', 'Extracción de Muela', 'Cirugía de Cataratas', 'Artroscopia de Rodilla', 'Colocación de Marcapasos', 'Cirugía de Hernia', 'Laparoscopia Abdominal', 'Transplante de Riñón']),
            'precio' => $this->faker->randomFloat(2, 100, 1500),
            'detalles' => $this->faker->text(100),
        ];
    }
}