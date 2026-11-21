<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacanteBitacora extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';

    protected $table = 'dbreclutamiento.vacante_bitacoras';

    protected $fillable = [
        'vacante_id',
        'accion',
        'descripcion',
        'datos_anteriores',
        'datos_nuevos',
        'usuario_id',
    ];

    protected function casts(): array
    {
        return [
            'vacante_id' => 'integer',
            'datos_anteriores' => 'array',
            'datos_nuevos' => 'array',
            'usuario_id' => 'integer',
        ];
    }

    public function vacante(): BelongsTo
    {
        return $this->belongsTo(
            Vacante::class,
            'vacante_id',
            'id'
        );
    }
}
