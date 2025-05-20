<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Procedimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'precio',
        'detalles',
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
        return "{$this->codigo} - {$this->nombre}";
    }
}