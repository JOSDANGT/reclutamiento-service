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

    const RECIBIDA = 1;
    const EN_REVISION = 2;
    const ENTREVISTA = 3;
    const SELECCIONADA = 4;
    const RECHAZADA = 5;
    const RETIRADA = 6;
    const CONTRATADA = 7;


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
