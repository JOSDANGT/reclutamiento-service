<?php

namespace App\Models;

use App\Traits\TieneBitacora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vacante extends Model
{
    use TieneBitacora, SoftDeletes;

    protected $connection = 'sqlsrv';

    protected $table = 'dbreclutamiento.vacantes';

    protected $fillable = [
        'titulo',
        'descripcion',
        'cantidad_plazas',
        'puesto_id',
        'salario_minimo',
        'salario_maximo',
        'modalidad',
        'tipo_contrato',
        'nivel_experiencia',
        'estado',
        'fecha_inicio',
        'fecha_limite',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_plazas' => 'integer',
            'salario_minimo' => 'decimal:2',
            'salario_maximo' => 'decimal:2',
            'fecha_inicio' => 'date',
            'fecha_limite' => 'date',
            'creado_por' => 'integer',
        ];
    }

    public function requisitos(): BelongsToMany
    {
        return $this->belongsToMany(
            Requisito::class,
            'dbreclutamiento.vacantes_has_requisitos',
            'vacante_id',
            'requisito_id'
        )
            ->withPivot(['id', 'obligatorio', 'orden'])
            ->withTimestamps();
    }

    public function bitacoras(): MorphMany
    {
        return $this->morphMany(
            Bitacora::class,
            'model',
            'model_type',
            'model_id'
        );
    }

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class, 'puesto_id');
    }
}
