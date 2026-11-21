<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostulacionEstado extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';

    protected $table = 'dbreclutamiento.postulacion_estados';

    protected $fillable = [
        'nombre',
        'descripcion',
        'orden',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'estado' => 'boolean',
        ];
    }

    /**
     * Postulaciones asociadas al estado.
     */
    public function postulaciones(): HasMany
    {
        return $this->hasMany(
            Postulacion::class,
            'estado_id'
        );
    }
}
