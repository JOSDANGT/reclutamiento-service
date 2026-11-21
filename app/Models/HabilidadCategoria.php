<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HabilidadCategoria extends Model
{
    use SoftDeletes;

    protected $connection = 'sqlsrv';
    protected $table = 'dbreclutamiento.habilidad_categorias';

    const DESARROLLO_BACKEND = 1;
    const DESARROLLO_FRONTEND = 2;
    const BASES_DE_DATOS = 3;
    const DEVOPS_E_INFRAESTRUCTURA = 4;
    const CIBERSEGURIDAD = 5;
    const ANALISIS_DE_DATOS = 6;
    const GESTION_PROYECTOS = 7;
    const HABILIDADES_BLANDAS = 8;


    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function habilidades(): HasMany
    {
        return $this->hasMany(
            Habilidad::class,
            'categoria_id'
        );
    }
}
