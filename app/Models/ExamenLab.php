<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ExamenLab extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre_examen',
        'precio',
        'resultado',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
    ];

    public function lineasTransaccion(): MorphMany
    {
        return $this->morphMany(LineaTransaccion::class, 'serviceable');
    }

    // Accesor para mostrar en Filament (ej. en MorphToSelect)
    public function getNombreConCodigoAttribute(): string
    {
        return "{$this->codigo} - {$this->nombre_examen}";
    }
}