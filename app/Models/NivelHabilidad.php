<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NivelHabilidad extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';

    protected $table = 'dbreclutamiento.nivel_habilidad';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function candidatosHabilidades(): HasMany
    {
        return $this->hasMany(
            CandidatoHasHabilidad::class,
            'nivel_habilidad_id'
        );
    }
}
