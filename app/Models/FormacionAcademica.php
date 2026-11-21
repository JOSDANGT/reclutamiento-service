<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class FormacionAcademica extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'sqlsrv';
    protected $table = 'dbreclutamiento.formaciones_academicas';

    protected $fillable = [
        'candidato_id',
        'institucion',
        'titulo',
        'nivel_academico',
        'area_estudio',
        'fecha_inicio',
        'fecha_fin',
        'en_curso',
        'finalizado',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date:Y-m-d',
            'fecha_fin' => 'date:Y-m-d',
            'en_curso' => 'boolean',
            'finalizado' => 'boolean',
        ];
    }

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(
            Candidato::class,
            'candidato_id'
        );
    }

    public function documentos(): MorphMany
    {
        return $this->morphMany(
            Documento::class,
            'model',
            'model_type',
            'model_id'
        );
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
}
