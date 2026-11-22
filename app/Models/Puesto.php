<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Puesto extends Model
{
    use SoftDeletes;

    protected $connection = 'sqlsrv';

    protected $table = 'dbreclutamiento.puestos';

    protected $fillable = [
        'unidad_id',
        'nombre',
        'descripcion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'unidad_id' => 'integer',
            'estado' => 'boolean',
        ];
    }

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    public function vacantes(): HasMany
    {
        return $this->hasMany(Vacante::class, 'puesto_id');
    }

    public function requisitos(): BelongsToMany
    {
        return $this->belongsToMany(
            Requisito::class,
            'dbreclutamiento.puestos_has_requisitos',
            'puesto_id',
            'requisito_id'
        )
            ->withPivot([
                'id',
                'obligatorio',
                'orden',
            ])
            ->withTimestamps();
    }
}
