<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LineaTransaccion extends Model
{
    protected $fillable = [
        'documento_transaccion_id',
        'serviceable_id',
        'serviceable_type',
        'cantidad',
        'precio_unitario',
        'subtotal'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function documentoTransaccion(): BelongsTo
    {
        return $this->belongsTo(DocumentoTransaccion::class);
    }

    public function serviceable(): MorphTo
    {
        return $this->morphTo();
    }
}