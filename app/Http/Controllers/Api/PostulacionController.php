<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostulacionRequest;
use App\Models\Candidato;
use App\Models\Documento;
use App\Models\Postulacion;
use App\Models\PostulacionEstado;
use App\Models\Vacante;
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

    public function retirar(Request $request, Postulacion $postulacion): JsonResponse {
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

            $postulacion->load('estado');

            $estadoActual = mb_strtoupper(
                trim($postulacion->estado?->nombre ?? '')
            );

            if (in_array($estadoActual, [
                'RETIRADA',
                'RECHAZADA',
                'SELECCIONADA',
            ], true)) {
                throw ValidationException::withMessages([
                    'postulacion' => 'Esta postulación ya no puede retirarse.',
                ]);
            }

            $estadoRetirada = PostulacionEstado::query()
                ->where('nombre', 'RETIRADA')
                ->first();

            if (!$estadoRetirada) {
                throw ValidationException::withMessages([
                    'estado' => 'No se encuentra configurado el estado RETIRADA.',
                ]);
            }

            $postulacion->update([
                'estado_id' => $estadoRetirada->id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Postulación retirada correctamente.',
                'data' => $postulacion->fresh(['estado']),
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

    public function cambiarEstado(Request $request, Postulacion $postulacion): JsonResponse {
        $datos = $request->validate([
            'estado_id' => [
                'required',
                'integer',
                Rule::exists('sqlsrv.dbreclutamiento.postulacion_estados', 'id'),
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        DB::beginTransaction();

        try {
            $postulacion = Postulacion::query()
                ->whereKey($postulacion->id)
                ->lockForUpdate()
                ->firstOrFail();

            $estadoAnterior = $postulacion->estado_id;

            if ((int) $estadoAnterior === (int) $datos['estado_id']) {
                throw ValidationException::withMessages([
                    'estado_id' => 'La postulación ya tiene ese estado.',
                ]);
            }

            $estadoNuevo = PostulacionEstado::findOrFail(
                $datos['estado_id']
            );

            if (mb_strtoupper(trim($estadoNuevo->nombre)) === 'RETIRADA') {
                throw ValidationException::withMessages([
                    'estado_id' => 'El retiro debe realizarlo el candidato.',
                ]);
            }

            $postulacion->update([
                'estado_id' => $estadoNuevo->id,
                'observaciones' => $datos['observaciones']
                    ?? $postulacion->observaciones,
                'revisado_por' => Auth::id(),
                'fecha_revision' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Estado actualizado correctamente.',
                'data' => $postulacion->fresh([
                    'estado',
                    'candidato',
                    'vacante',
                ]),
            ]);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }



    public function verDocumento(Postulacion $postulacion, Documento $documento): BinaryFileResponse {
        // El documento debe pertenecer al candidato
        // de la postulación que está revisando RR. HH.
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


}
