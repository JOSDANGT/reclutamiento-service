<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Candidato\StoreCandidatoRequest;
use App\Http\Requests\Candidato\UpdateCandidatoRequest;
use App\Services\CandidatoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CandidatoController extends Controller
{
    public function __construct(
        private readonly CandidatoService $candidatoService
    ) {}

    public function store(StoreCandidatoRequest $request): JsonResponse {
        $usuarioId = (int) $request->attributes->get(
            'identity_user_id'
        );

        $candidato = $this->candidatoService->crear(
            $request->validated(),
            $usuarioId
        );

        return response()->json([
            'success' => true,
            'message' => 'Perfil profesional creado correctamente.',
            'data' => $candidato,
        ], 201);
    }

    public function miPerfil(Request $request): JsonResponse
    {
        $usuarioId = (int) $request->attributes->get(
            'identity_user_id'
        );

        $candidato = $this->candidatoService->miPerfil(
            $usuarioId
        );

        if (!$candidato) {
            return response()->json([
                'success' => false,
                'message' => 'Todavía no tiene un perfil profesional.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $candidato,
        ]);
    }

    public function update(
        UpdateCandidatoRequest $request
    ): JsonResponse {
        $usuarioId = (int) $request->attributes->get(
            'identity_user_id'
        );

        $candidato = $this->candidatoService->actualizar(
            $request->validated(),
            $usuarioId
        );

        return response()->json([
            'success' => true,
            'message' => 'Perfil profesional actualizado correctamente.',
            'data' => $candidato,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'estado' => ['nullable', 'boolean'],
        ]);

        $candidatos = $this->candidatoService->listar(
            $filtros
        );

        return response()->json([
            'success' => true,
            'data' => $candidatos,
        ]);
    }

    public function show(int $candidato): JsonResponse
    {
        $perfil = $this->candidatoService->obtener(
            $candidato
        );

        return response()->json([
            'success' => true,
            'data' => $perfil,
        ]);
    }
}
