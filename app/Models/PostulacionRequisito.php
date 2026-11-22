<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostulacionRequisito extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbreclutamiento.postulacion_requisitos';

    protected $fillable = [
        'postulacion_id',
        'requisito_id',
        'cumple',
        'observacion',
        'evaluado_por',
        'evaluado_at',
    ];

    protected function casts(): array
    {
        return [
            'cumple' => 'boolean',
            'evaluado_at' => 'datetime',
            'postulacion_id' => 'integer',
            'requisito_id' => 'integer',
            'evaluado_por' => 'integer',
        ];
    }

    public function requisito(): BelongsTo
    {
        return $this->belongsTo(Requisito::class, 'requisito_id');
    }
}
