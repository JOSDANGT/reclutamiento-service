<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Requisito extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'sqlsrv';

    protected $table = 'dbreclutamiento.requisitos';

    protected $fillable = [
        'tipo_requisito_id',
        'nombre',
        'descripcion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'tipo_requisito_id' => 'integer',
            'estado' => 'boolean',
        ];
    }

    public function tipoRequisito(): BelongsTo
    {
        return $this->belongsTo(
            TipoRequisito::class,
            'tipo_requisito_id',
            'id'
        );
    }

    public function vacantes(): BelongsToMany
    {
        return $this->belongsToMany(
            Vacante::class,
            'dbreclutamiento.vacantes_has_requisitos',
            'requisito_id',
            'vacante_id'
        )
            ->withPivot([
                'id',
                'obligatorio',
                'orden',
            ])
            ->withTimestamps();
    }
}
