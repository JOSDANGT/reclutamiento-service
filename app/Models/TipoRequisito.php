<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoRequisito extends Model
{
    use SoftDeletes;

    protected $connection = 'sqlsrv';

    protected $table = 'dbreclutamiento.tipos_requisitos';

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

    public function requisitos(): HasMany
    {
        return $this->hasMany(
            Requisito::class,
            'tipo_requisito_id',
            'id'
        );
    }
}
