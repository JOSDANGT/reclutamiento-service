<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Bitacora extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv';
    protected $table = 'dbreclutamiento.bitacoras';

    protected $fillable = [
        'accion',
        'descripcion',
        'datos_anteriores',
        'datos_nuevos',
        'usuario_id',
    ];

    protected function casts(): array
    {
        return [
            'datos_anteriores' => 'array',
            'datos_nuevos' => 'array',
            'usuario_id' => 'integer',
        ];
    }

    public function model(): MorphTo
    {
        return $this->morphTo(
            __FUNCTION__,
            'model_type',
            'model_id'
        );
    }
}
