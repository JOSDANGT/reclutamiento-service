<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostulacionRequest;
use App\Models\Candidato;
use App\Models\Documento;
use App\Models\Postulacion;
use App\Models\PostulacionEstado;
use App\Models\Vacante;
use App\Services\BitacoraService;
use App\Services\PostulacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class PostulacionController extends Controller
{
    public function __construct(
        private readonly PostulacionService $service
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'vacante_id' => ['nullable', 'integer'],
            'estado_id' => ['nullable', 'integer'],
            'candidato_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $postulaciones = $this->service->listar($filtros);

        return response()->json([
            'data' => $postulaciones,
        ]);
    }


    public function verificarPostulacion(Request $request, Vacante $vacante): JsonResponse {
        $usuarioId = (int) $request->attributes->get('identity_user_id');

        if (!$usuarioId) {
            return response()->json([
                'message' => 'Usuario no autenticado.',
            ], 401);
        }

        $candidato = Candidato::query()
            ->where('user_id', $usuarioId)
            ->first();

        if (!$candidato) {
            return response()->json([
                'data' => [
                    'postulado' => false,
                    'postulacion' => null,
                ],
            ]);
        }

        $postulacion = Postulacion::query()
            ->with('estado')
            ->where('candidato_id', $candidato->id)
            ->where('vacante_id', $vacante->id)
            ->first();

        return response()->json([
            'data' => [
                'postulado' => $postulacion !== null,
                'postulacion' => $postulacion ? [
                    'id' => $postulacion->id,
                    'fecha_postulacion' => $postulacion->fecha_postulacion,
                    'estado' => $postulacion->estado?->nombre,
                ] : null,
            ],
        ]);
    }


    public function show(Postulacion $postulacion): JsonResponse
    {
        return response()->json([
            'data' => $this->service->obtener($postulacion),
        ]);
    }


    public function store(StorePostulacionRequest $request): JsonResponse
    {
        $usuarioId = $request->attributes->get('identity_user_id');

        if (!$usuarioId) {
            return response()->json([
                'message' => 'No se pudo identificar al usuario autenticado.',
            ], 401);
        }

        $postulacion = $this->service->crear(
            $request->validated(),
            (int) $usuarioId
        );

        return response()->json([
            'message' => 'Postulación registrada correctamente.',
            'data' => $postulacion,
        ], 201);
    }


    public function misPostulaciones(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'estado' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no autenticado.',
            ], 401);
        }

        $candidato = Candidato::query()
            ->where('user_id', $usuario->id)
            ->first();

        if (!$candidato) {
            return response()->json([
                'data' => [
                    'current_page' => 1,
                    'data' => [],
                    'total' => 0,
                    'last_page' => 1,
                ],
            ]);
        }

        $query = $candidato->postulaciones()
            ->with([
                'vacante.puesto.unidad',
                'estado',
            ]);

        if (!empty($filtros['search'])) {
            $search = trim($filtros['search']);

            $query->whereHas('vacante', function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%");
            });
        }

        if (!empty($filtros['estado'])) {
            $query->whereHas('estado', function ($q) use ($filtros) {
                $q->where('nombre', $filtros['estado']);
            });
        }

        $postulaciones = $query
            ->orderByDesc('fecha_postulacion')
            ->orderByDesc('id')
            ->paginate($filtros['per_page'] ?? 9);

        return response()->json([
            'data' => $postulaciones,
        ]);
    }

    public function retirar(Request $request, Postulacion $postulacion): JsonResponse
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no autenticado.',
            ], 401);
        }

        $candidato = Candidato::query()
            ->where('user_id', $usuario->id)
            ->first();

        if (!$candidato) {
            return response()->json([
                'message' => 'No tienes un perfil de candidato registrado.',
            ], 404);
        }

        DB::beginTransaction();

        try {
            $postulacion = $candidato->postulaciones()
                ->whereKey($postulacion->id)
                ->lockForUpdate()
                ->firstOrFail();

            $estadosPermitidos = [
                PostulacionEstado::RECIBIDA,
                PostulacionEstado::EN_REVISION,
                PostulacionEstado::ENTREVISTA,
            ];

            if (!in_array((int) $postulacion->estado_id, $estadosPermitidos, true)) {
                throw ValidationException::withMessages([
                    'postulacion' => 'Esta postulación ya no puede retirarse.',
                ]);
            }

            $postulacion->update([
                'estado_id' => PostulacionEstado::RETIRADA,
            ]);

            $postulacion->load('estado');

            DB::commit();


            return response()->json([
                'message' => 'Postulación retirada correctamente.',
                'data' => $postulacion,
            ]);

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function estados(): JsonResponse
    {
        return response()->json([
            'data' => PostulacionEstado::query()
                ->select('id', 'nombre')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function cambiarEstado(Request $request, Postulacion $postulacion): JsonResponse
    {
        $datos = $request->validate([
            'estado_id' => ['required', 'integer',],
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ]);
        $estadoNuevoId = (int)$datos['estado_id'];

        DB::beginTransaction();

        try {
            $postulacion = Postulacion::query()
                ->whereKey($postulacion->id)
                ->lockForUpdate()
                ->firstOrFail();


            $postulacion->update([
                'estado_id' => $estadoNuevoId,
                'observaciones' => $datos['observaciones'] ?? $postulacion->observaciones,
                'revisado_por' => auth()->id(),
                'fecha_revision' => now(),
            ]);

            $postulacion->load(['estado', 'candidato', 'vacante']);

            DB::commit();


            return response()->json([
                'message' => 'Estado actualizado correctamente.',
                'data' => $postulacion,
            ]);

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }



    public function verDocumento(Postulacion $postulacion, Documento $documento): BinaryFileResponse {

        $documento = $postulacion->candidato
            ->documentos()
            ->findOrFail($documento->id);

        $disco = Storage::disk($documento->disco);

        if (!$disco->exists($documento->ruta)) {
            abort(404, 'El archivo no está disponible.');
        }

        return response()->file(
            $disco->path($documento->ruta),
            [
                'Content-Type' => $documento->mime_type
                    ?: 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function registrarDecisionOferta(
        Request $request,
        Postulacion $postulacion,
        BitacoraService $bitacoraService
    ): JsonResponse {
        $datos = $request->validate([
            'decision' => ['required', Rule::in(['ACEPTADA', 'RECHAZADA'])],
            'fecha_ingreso' => [
                'exclude_unless:decision,ACEPTADA',
                'required',
                'date_format:Y-m-d',
            ],
            'observaciones' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::beginTransaction();

        try {
            $postulacion = Postulacion::query()
                ->with('estado')
                ->lockForUpdate()
                ->findOrFail($postulacion->id);

            if ((int) $postulacion->estado_id !== PostulacionEstado::SELECCIONADA) {
                throw ValidationException::withMessages([
                    'postulacion' => 'La postulación debe estar seleccionada para registrar la decisión de oferta.',
                ]);
            }

            $anteriores = [
                'estado_id' => $postulacion->estado_id,
                'estado' => $postulacion->estado->nombre,
                'fecha_ingreso' => $postulacion->fecha_ingreso,
            ];

            if ($datos['decision'] === 'ACEPTADA') {
                $postulacion->estado_id = PostulacionEstado::CONTRATADA;
                $postulacion->fecha_ingreso = $datos['fecha_ingreso'];

                $accion = 'CONTRATACION_CONFIRMADA';
                $mensaje = 'Contratación confirmada.';
            } else {
                $postulacion->estado_id = PostulacionEstado::OFERTA_RECHAZADA;
                $postulacion->fecha_ingreso = null;

                $accion = 'OFERTA RECHAZADA';
                $mensaje = 'Rechazo de oferta registrado.';
            }

            $postulacion->save();
            $postulacion->load('estado');

            $bitacoraService->registrar(
                $postulacion,
                $accion,
                $mensaje,
                $anteriores,
                [
                    'estado_id' => $postulacion->estado_id,
                    'estado' => $postulacion->estado->nombre,
                    'fecha_ingreso' => $postulacion->fecha_ingreso,
                    'observaciones' => $datos['observaciones'] ?? null,
                ],
                auth()->id()
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json([
            'message' => $mensaje,
            'data' => [
                'postulacion_id' => $postulacion->id,
                'estado' => $postulacion->estado->nombre,
                'fecha_ingreso' => $postulacion->fecha_ingreso,
            ],
        ]);
    }


}
