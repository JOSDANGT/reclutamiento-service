<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Habilidad extends Model
{
    use SoftDeletes;

    protected $connection = 'sqlsrv';
    protected $table = 'dbreclutamiento.habilidades';

    protected $fillable = [
        'nombre',
        'categoria',
        'descripcion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
            'categoria_id' => 'integer',
        ];
    }

    public function candidatos(): BelongsToMany
    {
        return $this->belongsToMany(
            Candidato::class,
            'dbreclutamiento.candidatos_has_habilidades',
            'habilidad_id',
            'candidato_id'
        )->withPivot([
            'nivel',
            'anios_experiencia',
        ])->withTimestamps();
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(
            HabilidadCategoria::class,
            'categoria_id'
        );
    }
}
