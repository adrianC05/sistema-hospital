<?php

namespace Database\Factories;

use App\Models\ImgRayosX;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImgRayosXFactory extends Factory
{
    protected $model = ImgRayosX::class;

    public function definition(): array
    {
        return [
            'codigo' => 'RX-' . $this->faker->unique()->numberBetween(100, 999),
            'tipo_imagen' => $this->faker->randomElement(['Radiografía de Tórax', 'Tomografía Craneal', 'Resonancia Magnética Abdominal', 'Radiografía de Columna', 'Ultrasonido Abdominal', 'Radiografía Dental', 'Radiografía de Extremidades', 'Radiografía de Pelvis', 'Radiografía de Mano', 'Radiografía de Pie']),
            'precio' => $this->faker->randomFloat(2, 50, 800),
            'informe' => $this->faker->paragraph(),
        ];
    }
}