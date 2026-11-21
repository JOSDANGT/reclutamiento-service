<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Postulacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'sqlsrv';

    protected $table = 'dbreclutamiento.postulaciones';

    protected $fillable = [
        'candidato_id',
        'vacante_id',
        'estado_id',
        'fecha_postulacion',
        'carta_presentacion',
        'observaciones',
        'revisado_por',
        'fecha_revision',
    ];

    protected function casts(): array
    {
        return [
            'candidato_id' => 'integer',
            'vacante_id' => 'integer',
            'estado_id' => 'integer',
            'fecha_postulacion' => 'datetime',
            'fecha_revision' => 'datetime',
            'revisado_por' => 'integer',
        ];
    }

    /**
     * Candidato que realizó la postulación.
     */
    public function candidato(): BelongsTo
    {
        return $this->belongsTo(
            Candidato::class,
            'candidato_id'
        );
    }

    /**
     * Vacante a la que se postuló.
     */
    public function vacante(): BelongsTo
    {
        return $this->belongsTo(
            Vacante::class,
            'vacante_id'
        );
    }

    /**
     * Estado actual de la postulación.
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(
            PostulacionEstado::class,
            'estado_id'
        );
    }

    /**
     * Documentos asociados a la postulación.
     * Relación polimórfica: model_type y model_id.
     */
    public function documentos(): MorphMany
    {
        return $this->morphMany(
            Documento::class,
            'model',
            'model_type',
            'model_id'
        );
    }

    /**
     * Historial de acciones de la postulación.
     * Relación polimórfica: model_type y model_id.
     */
    public function bitacoras(): MorphMany
    {
        return $this->morphMany(
            Bitacora::class,
            'model',
            'model_type',
            'model_id'
        );
    }
}
