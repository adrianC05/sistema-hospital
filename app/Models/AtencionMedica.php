<?php

// app/Models/AtencionMedica.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AtencionMedica extends Model
{
    // Asegúrate de que 'medico_id' esté en $fillable, no 'medico'
    protected $fillable = [
        'codigo',
        'descripcion',
        'precio',
        'medico_id',
        'especialidad',
        'notas'
    ];

    // Relación con el usuario (médico)
    public function medico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'medico_id');
    }

    // Relación polimórfica (como serviceable)
    public function lineasTransaccion(): MorphMany
    {
        return $this->morphMany(LineaTransaccion::class, 'serviceable');
    }

    // Para Filament, si quieres mostrar el nombre del médico + código en el MorphToSelect
    public function getDescripcionConCodigoAttribute(): string
    {
        $medicoNombre = $this->medico ? $this->medico->nombre_completo : 'N/A';
        return "{$this->codigo} - {$this->descripcion} (Dr. {$medicoNombre})";
    }
}