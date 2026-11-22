<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Postulacion;
use App\Models\PostulacionEstado;
use App\Models\User;
use App\Services\BitacoraService;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AgendaController extends Controller
{
    private const TIPO = 'ENTREVISTA';

    public function __construct(
        private BitacoraService $bitacoraService
    ) {
    }

    public function index(Postulacion $postulacion): JsonResponse
    {
        $agendas = $postulacion->agendas()
            ->with('responsable')
            ->where('tipo', Agenda::TIPO_ENTREVISTA)
            ->orderBy('fecha_hora')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => $agendas,
        ]);
    }

    public function responsables(): JsonResponse
    {
        $usuarios = User::query()
            ->role('RECLUTADOR')
            ->get()
            ->sortBy('nombre_completo', SORT_NATURAL | SORT_FLAG_CASE)
            ->map(fn (User $usuario) => [
                'id' => $usuario->id,
                'nombre_completo' => $usuario->nombre_completo,
            ])
            ->values();

        return response()->json([
            'data' => $usuarios,
        ]);
    }

    public function store(
        Request $request,
        Postulacion $postulacion
    ): JsonResponse {
        $datos = $this->validarDatosCita($request, true);
        $usuarioId = $this->usuarioGestion($request);

        return DB::transaction(function () use ($postulacion, $datos, $usuarioId) {
            $postulacion = $this->bloquearPostulacion($postulacion);

            $this->validarProgramacion($postulacion, (int) $datos['etapa']);

            $agenda = $postulacion->agendas()->create([
                'tipo' => self::TIPO,
                'etapa' => (int) $datos['etapa'],
                'usuario_id' => (int) $datos['usuario_id'],
                'fecha_hora' => $datos['fecha_hora'],
                'modalidad' => $datos['modalidad'],
                'lugar_enlace' => $datos['lugar_enlace'] ?? null,
                'estado' => Agenda::PROGRAMADA,
                'creado_por' => $usuarioId,
            ]);

            $this->bitacoraService->registrar(
                $agenda,
                'ENTREVISTA_PROGRAMADA',
                'Se programó la entrevista.',
                null,
                $agenda->only([
                    'postulacion_id',
                    'etapa',
                    'usuario_id',
                    'fecha_hora',
                    'modalidad',
                    'lugar_enlace',
                    'estado',
                ]),
                $usuarioId
            );

            return $this->respuestaAgenda(
                $agenda,
                'Entrevista programada correctamente.',
                201
            );
        });
    }

    public function reprogramar(
        Request $request,
        Postulacion $postulacion,
        Agenda $agenda
    ): JsonResponse {
        $datos = $this->validarDatosCita($request);

        return $this->gestionarAgenda(
            $request,
            $postulacion,
            $agenda,
            function (Postulacion $postulacion, Agenda $agenda, int $usuarioId) use ($datos) {
                $this->validarAgendaProgramada($agenda);

                $campos = [
                    'usuario_id',
                    'fecha_hora',
                    'modalidad',
                    'lugar_enlace',
                ];

                $anteriores = $agenda->only($campos);

                $agenda->fill([
                    'usuario_id' => (int) $datos['usuario_id'],
                    'fecha_hora' => $datos['fecha_hora'],
                    'modalidad' => $datos['modalidad'],
                    'lugar_enlace' => $datos['lugar_enlace'] ?? null,
                ]);

                $modificados = array_keys($agenda->getDirty());

                if ($modificados === []) {
                    return $this->respuestaAgenda(
                        $agenda,
                        'La entrevista ya tiene los datos indicados.'
                    );
                }

                $soloResponsable = $modificados === ['usuario_id'];

                $agenda->gestionado_por = $usuarioId;
                $agenda->save();

                $this->bitacoraService->registrar(
                    $agenda,
                    $soloResponsable
                        ? 'ENTREVISTA_REASIGNADA'
                        : 'ENTREVISTA_REPROGRAMADA',
                    $soloResponsable
                        ? 'Se cambió el responsable de la entrevista.'
                        : 'Se actualizaron los datos de la entrevista.',
                    array_intersect_key($anteriores, array_flip($modificados)),
                    $agenda->only($modificados),
                    $usuarioId
                );

                return $this->respuestaAgenda(
                    $agenda,
                    'Entrevista actualizada correctamente.'
                );
            }
        );
    }

    public function registrarResultado(
        Request $request,
        Postulacion $postulacion,
        Agenda $agenda
    ): JsonResponse {
        $datos = $request->validate([
            'resultado' => [
                'required',
                Rule::in([Agenda::APROBADA, Agenda::NO_APROBADA]),
            ],
            'observaciones' => ['required', 'string', 'max:5000'],
        ]);

        return $this->gestionarAgenda(
            $request,
            $postulacion,
            $agenda,
            function (Postulacion $postulacion, Agenda $agenda, int $usuarioId) use ($datos) {
                $this->validarAgendaProgramada($agenda);

                $this->actualizarConBitacora(
                    $agenda,
                    [
                        'estado' => Agenda::REALIZADA,
                        'resultado' => $datos['resultado'],
                        'observaciones' => $datos['observaciones'],
                    ],
                    'ENTREVISTA_RESULTADO',
                    'Se registró el resultado de la entrevista.',
                    $usuarioId
                );

                return $this->respuestaAgenda(
                    $agenda,
                    'Resultado registrado correctamente.'
                );
            }
        );
    }

    public function cancelar(
        Request $request,
        Postulacion $postulacion,
        Agenda $agenda
    ): JsonResponse {
        $datos = $request->validate([
            'motivo_cancelacion' => ['required', 'string', 'max:5000'],
        ]);

        return $this->gestionarAgenda(
            $request,
            $postulacion,
            $agenda,
            function (Postulacion $postulacion, Agenda $agenda, int $usuarioId) use ($datos) {
                $this->validarAgendaProgramada($agenda);

                $this->actualizarConBitacora(
                    $agenda,
                    [
                        'estado' => Agenda::CANCELADA,
                        'motivo_cancelacion' => $datos['motivo_cancelacion'],
                    ],
                    'ENTREVISTA_CANCELADA',
                    'Se canceló la cita de entrevista.',
                    $usuarioId
                );

                return $this->respuestaAgenda(
                    $agenda,
                    'Entrevista cancelada correctamente.'
                );
            }
        );
    }

    public function autorizarContinuidad(
        Request $request,
        Postulacion $postulacion,
        Agenda $agenda
    ): JsonResponse {
        $datos = $request->validate([
            'justificacion_continuidad' => ['required', 'string', 'max:5000'],
        ]);

        return $this->gestionarAgenda(
            $request,
            $postulacion,
            $agenda,
            function (Postulacion $postulacion, Agenda $agenda, int $usuarioId) use ($datos) {
                $this->validarDecisionPendiente($postulacion, $agenda);

                $this->actualizarConBitacora(
                    $agenda,
                    [
                        'continuidad_autorizada' => true,
                        'justificacion_continuidad' => $datos['justificacion_continuidad'],
                        'continuidad_autorizada_por' => $usuarioId,
                        'continuidad_autorizada_en' => now(),
                    ],
                    'ENTREVISTA_CONTINUIDAD',
                    'Se autorizó continuar después de una entrevista no aprobada.',
                    $usuarioId
                );

                return $this->respuestaAgenda(
                    $agenda,
                    'Continuidad autorizada correctamente.'
                );
            }
        );
    }

    public function darDeBaja(
        Request $request,
        Postulacion $postulacion,
        Agenda $agenda
    ): JsonResponse {
        $datos = $request->validate([
            'motivo_baja' => ['required', 'string', 'max:5000'],
        ]);

        return $this->gestionarAgenda(
            $request,
            $postulacion,
            $agenda,
            function (Postulacion $postulacion, Agenda $agenda, int $usuarioId) use ($datos) {
                $this->validarDecisionPendiente($postulacion, $agenda);

                $estadoAnterior = $postulacion->estado;

                $postulacion->estado_id = PostulacionEstado::RECHAZADA;
                $postulacion->save();
                $postulacion->load('estado');

                $this->bitacoraService->registrar(
                    $postulacion,
                    'POSTULACION_BAJA',
                    'Se dio de baja la postulación por una entrevista no aprobada.',
                    [
                        'estado_id' => $estadoAnterior->getKey(),
                        'estado' => $estadoAnterior->nombre,
                    ],
                    [
                        'estado_id' => $postulacion->estado_id,
                        'estado' => $postulacion->estado->nombre,
                        'agenda_id' => $agenda->id,
                        'etapa' => (int) $agenda->etapa,
                        'motivo_baja' => $datos['motivo_baja'],
                    ],
                    $usuarioId
                );

                return response()->json([
                    'message' => 'Postulación dada de baja correctamente.',
                    'data' => [
                        'postulacion_id' => $postulacion->id,
                        'agenda_id' => $agenda->id,
                        'estado' => $postulacion->estado->nombre,
                    ],
                ]);
            }
        );
    }

    private function validarDatosCita(
        Request $request,
        bool $validarEtapa = false
    ): array {
        $reglas = [
            'usuario_id' => [
                'required',
                'integer',
                Rule::exists(User::class, 'id'),
            ],
            'fecha_hora' => ['required', 'date'],
            'modalidad' => [
                'required',
                Rule::in(['PRESENCIAL', 'VIRTUAL', 'TELEFONICA']),
            ],
            'lugar_enlace' => ['nullable', 'string', 'max:500'],
        ];

        if ($validarEtapa) {
            $reglas['etapa'] = [
                'required',
                'integer',
                Rule::in(array_keys(Agenda::ETAPAS)),
            ];
        }

        $datos = $request->validate($reglas);

        // El datetime-local del formulario representa la hora de Guatemala.
        $fechaHora = Carbon::parse(
            $datos['fecha_hora'],
            'America/Guatemala'
        )->utc();

        if ($fechaHora->lessThanOrEqualTo(now())) {
            throw ValidationException::withMessages([
                'fecha_hora' => 'La fecha y hora de la entrevista debe ser futura.',
            ]);
        }

        $datos['fecha_hora'] = $fechaHora;

        return $datos;
    }

    private function usuarioGestion(Request $request): int
    {
        $usuarioId = filter_var(
            $request->attributes->get('identity_user_id'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        abort_if($usuarioId === false, 401, 'No se identificó al usuario.');

        return $usuarioId;
    }

    private function bloquearPostulacion(Postulacion $postulacion): Postulacion
    {
        $postulacion = Postulacion::query()
            ->with('estado')
            ->whereKey($postulacion->id)
            ->lockForUpdate()
            ->firstOrFail();

        $estado = mb_strtoupper(trim($postulacion->estado?->nombre ?? ''));

        if ($estado !== 'ENTREVISTA') {
            throw ValidationException::withMessages([
                'postulacion' => 'La postulación debe encontrarse en entrevista.',
            ]);
        }

        return $postulacion;
    }

    private function gestionarAgenda(
        Request $request,
        Postulacion $postulacion,
        Agenda $agenda,
        Closure $operacion
    ): JsonResponse {
        $usuarioId = $this->usuarioGestion($request);

        return DB::transaction(function () use (
            $postulacion,
            $agenda,
            $operacion,
            $usuarioId
        ) {
            $postulacion = $this->bloquearPostulacion($postulacion);

            $agenda = $postulacion->agendas()
                ->where('tipo', self::TIPO)
                ->whereKey($agenda->id)
                ->lockForUpdate()
                ->firstOrFail();

            return $operacion($postulacion, $agenda, $usuarioId);
        });
    }

    private function validarAgendaProgramada(Agenda $agenda): void
    {
        if ($agenda->estado !== Agenda::PROGRAMADA) {
            throw ValidationException::withMessages([
                'agenda' => 'La entrevista ya fue gestionada.',
            ]);
        }
    }

    private function siguienteEtapa(Postulacion $postulacion): ?int
    {
        $resueltas = $postulacion->agendas()
            ->where('tipo', self::TIPO)
            ->where('estado', Agenda::REALIZADA)
            ->where(function ($query) {
                $query->where('resultado', Agenda::APROBADA)
                    ->orWhere(function ($query) {
                        $query->where('resultado', Agenda::NO_APROBADA)
                            ->where('continuidad_autorizada', true);
                    });
            })
            ->pluck('etapa')
            ->map(fn ($etapa) => (int) $etapa);

        foreach (array_keys(Agenda::ETAPAS) as $etapa) {
            if (!$resueltas->containsStrict((int) $etapa)) {
                return (int) $etapa;
            }
        }

        return null;
    }

    private function validarProgramacion(
        Postulacion $postulacion,
        int $etapa
    ): void {
        $siguiente = $this->siguienteEtapa($postulacion);

        if ($siguiente === null) {
            throw ValidationException::withMessages([
                'etapa' => 'Todas las entrevistas están completadas.',
            ]);
        }

        if ($etapa !== $siguiente) {
            throw ValidationException::withMessages([
                'etapa' => 'Solo puedes programar la etapa actual.',
            ]);
        }

        $entrevistas = $postulacion->agendas()
            ->where('tipo', self::TIPO)
            ->where('etapa', $etapa);

        if ((clone $entrevistas)->where('estado', Agenda::PROGRAMADA)->exists()) {
            throw ValidationException::withMessages([
                'etapa' => 'Ya existe una entrevista programada para esta etapa.',
            ]);
        }

        $pendienteDecision = $entrevistas
            ->where('estado', Agenda::REALIZADA)
            ->where('resultado', Agenda::NO_APROBADA)
            ->where('continuidad_autorizada', false)
            ->exists();

        if ($pendienteDecision) {
            throw ValidationException::withMessages([
                'etapa' => 'Debes autorizar la continuidad o dar de baja la postulación.',
            ]);
        }
    }

    private function validarDecisionPendiente(
        Postulacion $postulacion,
        Agenda $agenda
    ): void {
        if (
            $agenda->estado !== Agenda::REALIZADA
            || $agenda->resultado !== Agenda::NO_APROBADA
            || $agenda->continuidad_autorizada
        ) {
            throw ValidationException::withMessages([
                'agenda' => 'La entrevista no tiene una decisión de continuidad pendiente.',
            ]);
        }

        if ($this->siguienteEtapa($postulacion) !== (int) $agenda->etapa) {
            throw ValidationException::withMessages([
                'agenda' => 'La entrevista ya no corresponde a la etapa actual.',
            ]);
        }
    }

    private function actualizarConBitacora(
        Agenda $agenda,
        array $cambios,
        string $accion,
        string $descripcion,
        int $usuarioId
    ): void {
        $campos = array_keys($cambios);
        $anteriores = $agenda->only($campos);

        $agenda->fill($cambios);
        $agenda->gestionado_por = $usuarioId;
        $agenda->save();

        $this->bitacoraService->registrar(
            $agenda,
            $accion,
            $descripcion,
            $anteriores,
            $agenda->only($campos),
            $usuarioId
        );
    }

    private function respuestaAgenda(
        Agenda $agenda,
        string $mensaje,
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'message' => $mensaje,
            'data' => $agenda->load('responsable'),
        ], $status);
    }
}
