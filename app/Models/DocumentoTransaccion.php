<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentoTransaccion extends Model
{

    protected $fillable = [
        'paciente_id', 'numero', 'fecha', 'valor_total', 'estado', 'tipo'
    ];

    protected $casts = [
        'fecha' => 'date',
        'valor_total' => 'decimal:2',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paciente_id');
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(LineaTransaccion::class);
    }
}
