<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ImgRayosX extends Model
{
    use HasFactory;

    protected $table = 'img_rayos_x_e_s'; // Especificar nombre de tabla si es irregular

    protected $fillable = [
        'codigo',
        'tipo_imagen',
        'precio',
        'informe',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
    ];

    public function lineasTransaccion(): MorphMany
    {
        return $this->morphMany(LineaTransaccion::class, 'serviceable');
    }

    // Accesor para mostrar en Filament
    public function getNombreConCodigoAttribute(): string
    {
        return "{$this->codigo} - {$this->tipo_imagen}";
    }
}