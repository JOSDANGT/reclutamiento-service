<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Documento extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'sqlsrv';
    protected $table = 'dbreclutamiento.documentos';

    protected $fillable = [
        'tipo_documento',
        'nombre_original',
        'ruta',
        'disco',
        'mime_type',
        'tamanio_bytes',
        'es_principal',
    ];

    protected function casts(): array
    {
        return [
            'tamanio_bytes' => 'integer',
            'es_principal' => 'boolean',
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
