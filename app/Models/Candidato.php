<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Candidato extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'sqlsrv';
    protected $table = 'dbreclutamiento.candidatos';

    protected $fillable = [
        'user_id',
        'titulo_profesional',
        'resumen_profesional',
        'anios_experiencia',
        'nivel_academico',
        'telefono_contacto',
        'pais',
        'departamento',
        'municipio',
        'disponibilidad',
        'pretension_salarial',
        'linkedin_url',
        'github_url',
        'portafolio_url',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'anios_experiencia' => 'integer',
            'pretension_salarial' => 'decimal:2',
            'estado' => 'boolean',
        ];
    }

    public function habilidades(): BelongsToMany
    {
        return $this->belongsToMany(
            Habilidad::class,
            'dbreclutamiento.candidatos_has_habilidades',
            'candidato_id',
            'habilidad_id'
        )
            ->withPivot([
                'nivel_habilidad_id',
                'anios_experiencia',
            ])
            ->withTimestamps();
    }

    public function experienciasLaborales(): HasMany
    {
        return $this->hasMany(
            ExperienciaLaboral::class,
            'candidato_id'
        );
    }

    public function formacionesAcademicas(): HasMany
    {
        return $this->hasMany(
            FormacionAcademica::class,
            'candidato_id'
        );
    }

    public function postulaciones(): HasMany
    {
        return $this->hasMany(
            Postulacion::class,
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
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
