<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $connection = 'sqlsrv';

    protected $table = 'dbreclutamiento.users';

    protected $guard_name = 'web';

    protected $appends = [
        'nombre_completo',
    ];

    protected $fillable = [
        'usuario_dominio',
        'puesto_id',
        'primer_nombre',
        'segundo_nombre',
        'tercer_nombre',
        'primer_apellido',
        'segundo_apellido',
        'apellido_casada',
        'email',
        'telefono',
        'password',
        'estado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'estado' => 'boolean',
            'ultimo_acceso_at' => 'datetime',
        ];
    }

    public function getNombreCompletoAttribute(): string
    {
        return implode(' ', array_filter([
            $this->primer_nombre,
            $this->segundo_nombre,
            $this->tercer_nombre,
            $this->primer_apellido,
            $this->segundo_apellido,
            $this->apellido_casada,
        ], fn ($valor) => filled($valor)));
    }

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'puesto_id');
    }

    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class, 'candidato_id');
    }
}
