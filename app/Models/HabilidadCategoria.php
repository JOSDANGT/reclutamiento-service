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
