<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'ci',
        'fecha_nacimiento',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    # Un usuario puede tener muchos documentos transaccionales
    public function documentoTransaccion()
    {
        return $this->hasMany(DocumentoTransaccion::class, 'paciente_id');
    }

    // Un paciente puede tener muchas atenciones medicas
    public function atencionesMedicas()
    {
        return $this->hasMany(AtencionMedica::class);
    }

    // Atributo calculado para nombre completo, útil en Filament
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->name} {$this->last_name}";
    }
}
