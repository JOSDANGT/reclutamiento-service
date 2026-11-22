<?php

namespace App\Models;

use App\Traits\TieneBitacora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agenda extends Model
{
    use TieneBitacora;

    protected $table = 'dbreclutamiento.agendas';

    public const TIPO_ENTREVISTA = 'ENTREVISTA';

    public const ETAPA_RECURSOS_HUMANOS = 1;
    public const ETAPA_JEFE_INMEDIATO = 2;
    public const ETAPA_ENTREVISTA_TECNICA = 3;

    public const ETAPAS = [
        self::ETAPA_RECURSOS_HUMANOS => 'Recursos Humanos',
        self::ETAPA_JEFE_INMEDIATO => 'Jefe inmediato',
        self::ETAPA_ENTREVISTA_TECNICA => 'Entrevista técnica',
    ];

    public const PROGRAMADA = 'PROGRAMADA';
    public const REALIZADA = 'REALIZADA';
    public const CANCELADA = 'CANCELADA';

    public const APROBADA = 'APROBADA';
    public const NO_APROBADA = 'NO_APROBADA';

    protected $fillable = [
        'postulacion_id',
        'tipo',
        'etapa',
        'usuario_id',
        'fecha_hora',
        'modalidad',
        'lugar_enlace',
        'estado',
        'resultado',
        'observaciones',
        'motivo_cancelacion',
        'continuidad_autorizada',
        'justificacion_continuidad',
        'continuidad_autorizada_por',
        'continuidad_autorizada_en',
        'creado_por',
        'gestionado_por',
    ];

    protected $casts = [
        'etapa' => 'integer',
        'fecha_hora' => 'datetime',
        'continuidad_autorizada' => 'boolean',
        'continuidad_autorizada_en' => 'datetime',
    ];

    public function postulacion(): BelongsTo
    {
        return $this->belongsTo(Postulacion::class, 'postulacion_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function gestionadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestionado_por');
    }

    public function autorizadorContinuidad(): BelongsTo
    {
        return $this->belongsTo(User::class, 'continuidad_autorizada_por');
    }

    public function permiteAvanzar(): bool
    {
        if ($this->estado !== self::ESTADO_REALIZADA) {
            return false;
        }

        return $this->resultado === self::RESULTADO_APROBADA
            || (
                $this->resultado === self::RESULTADO_NO_APROBADA
                && $this->continuidad_autorizada
            );
    }

    public function requiereDecisionDeContinuidad(): bool
    {
        return $this->estado === self::ESTADO_REALIZADA
            && $this->resultado === self::RESULTADO_NO_APROBADA
            && !$this->continuidad_autorizada;
    }
}
