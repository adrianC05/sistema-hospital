<?php

// app/Models/Medicamento.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
    protected $fillable = ['codigo', 'nombre', 'precio', 'dosis', 'detalles', 'stock'];

    public function lineasTransaccion()
    {
        return $this->morphMany(LineaTransaccion::class, 'serviceable');
    }

    // Para Filament, puedes querer un atributo que combine código y nombre
    public function getNombreConCodigoAttribute(): string
    {
        return "{$this->codigo} - {$this->nombre}";
    }
}