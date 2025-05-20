<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $fillable = ['descripcion', 'codigo', 'precio', 'tipo_servicio'];
    
    public function Medicamento()
    {
        return $this->hasOne(Medicamento::class);
    }

    public function atencionMedica()
    {
        return $this->hasOne(AtencionMedica::class);
    }

    public function examenLab()
    {
        return $this->hasOne(ExamenLab::class);
    }

    public function procedimiento()
    {
        return $this->hasOne(Procedimiento::class);
    }

    public function imgRayosX()
    {
        return $this->hasOne(ImgRayosX::class);
    }

    public function lineasTransaccion()
    {
        return $this->hasMany(LineaTransaccion::class);
    }
}
