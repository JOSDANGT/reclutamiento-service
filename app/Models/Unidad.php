<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unidad extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbreclutamiento.unidades';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function vacantes(): HasMany
    {
        return $this->hasMany(Vacante::class, 'unidad_id');
    }

    public function scopeActives($query)
    {
        return $query->where('estado', true);
    }
}
