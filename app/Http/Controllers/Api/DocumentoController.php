<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidato;
use App\Models\Documento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class DocumentoController extends Controller
{
    private const DISCO = 'local';

    private const TIPOS = [
        'CV',
        'CERTIFICADO',
        'CONSTANCIA',
        'OTRO',
    ];

    private function obtenerCandidato(Request $request): Candidato
    {
        $usuarioId = $request->attributes->get('identity_user_id');

        if (!$usuarioId) {
            throw ValidationException::withMessages([
                'usuario' => 'No se pudo identificar al usuario autenticado.',
            ]);
        }

        $candidato = Candidato::query()
            ->where('user_id', (int) $usuarioId)
            ->first();

        if (!$candidato) {
            throw ValidationException::withMessages([
                'perfil' => 'Primero debes registrar tu perfil de candidato.',
            ]);
        }

        return $candidato;
    }

    public function index(Request $request): JsonResponse
    {
        $candidato = $this->obtenerCandidato($request);

        $documentos = $candidato->documentos()
            ->orderByDesc('es_principal')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($documento) => $this->formatearDocumento($documento));

        return response()->json([
            'data' => $documentos,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'archivo' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:10240',
            ],
            'tipo_documento' => [
                'required',
                'string',
                Rule::in(self::TIPOS),
            ],
            'es_principal' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $candidato = $this->obtenerCandidato($request);

        $archivo = $request->file('archivo');
        $disco = self::DISCO;
        $ruta = null;

        $esPrincipal = $request->boolean('es_principal');

        DB::beginTransaction();

        try {
            // El servidor decide dónde almacenar el archivo.
            $ruta = $archivo->store(
                "candidatos/{$candidato->id}/documentos",
                $disco
            );

            if (!$ruta) {
                throw new \RuntimeException(
                    'No se pudo almacenar el archivo.'
                );
            }

            // Solo puede existir un documento principal por candidato.
            if ($esPrincipal) {
                $candidato->documentos()
                    ->where('es_principal', true)
                    ->update([
                        'es_principal' => false,
                    ]);
            }

            $documento = $candidato->documentos()->create([
                'tipo_documento' => $datos['tipo_documento'],
                'nombre_original' => $archivo->getClientOriginalName(),
                'ruta' => $ruta,
                'disco' => $disco,
                'mime_type' => $archivo->getMimeType(),
                'tamanio_bytes' => $archivo->getSize(),
                'es_principal' => $esPrincipal,
            ]);

            $candidato->bitacoras()->create([
                'accion' => 'DOCUMENTO_AGREGADO',
                'descripcion' => 'Se agregó un documento al perfil del candidato.',
                'datos_anteriores' => null,
                'datos_nuevos' => [
                    'documento_id' => $documento->id,
                    'nombre_original' => $documento->nombre_original,
                    'tipo_documento' => $documento->tipo_documento,
                    'es_principal' => $documento->es_principal,
                ],
                'usuario_id' => (int) $request->attributes->get('identity_user_id'),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Documento cargado correctamente.',
                'data' => $this->formatearDocumento($documento),
            ], 201);

        } catch (Throwable $e) {
            DB::rollBack();

            if ($ruta) {
                Storage::disk($disco)->delete($ruta);
            }

            throw $e;
        }
    }

    public function show(
        Request $request,
        Documento $documento
    ): BinaryFileResponse {
        $candidato = $this->obtenerCandidato($request);

        $documento = $candidato->documentos()
            ->findOrFail($documento->id);

        if (!Storage::disk($documento->disco)->exists($documento->ruta)) {
            abort(404, 'El archivo no se encuentra disponible.');
        }

        return response()->file(
            Storage::disk($documento->disco)->path($documento->ruta),
            [
                'Content-Type' => $documento->mime_type
                    ?: 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="documento."'
                    . pathinfo($documento->nombre_original, PATHINFO_EXTENSION),
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function destroy(
        Request $request,
        Documento $documento
    ): JsonResponse {
        $candidato = $this->obtenerCandidato($request);

        DB::beginTransaction();

        try {
            $documento = $candidato->documentos()
                ->findOrFail($documento->id);

            $datosAnteriores = [
                'documento_id' => $documento->id,
                'nombre_original' => $documento->nombre_original,
                'tipo_documento' => $documento->tipo_documento,
                'es_principal' => $documento->es_principal,
            ];

            $documento->delete();

            $candidato->bitacoras()->create([
                'accion' => 'DOCUMENTO_ELIMINADO',
                'descripcion' => 'Se eliminó un documento del perfil del candidato.',
                'datos_anteriores' => $datosAnteriores,
                'datos_nuevos' => null,
                'usuario_id' => (int) $request->attributes->get('identity_user_id'),
            ]);

            DB::commit();

            // Es una eliminación lógica: se conserva el archivo
            // para mantener la trazabilidad.
            return response()->json([
                'message' => 'Documento eliminado correctamente.',
            ]);

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function formatearDocumento(Documento $documento): array
    {
        return [
            'id' => $documento->id,
            'tipo_documento' => $documento->tipo_documento,
            'nombre_original' => $documento->nombre_original,
            'mime_type' => $documento->mime_type,
            'tamanio_bytes' => $documento->tamanio_bytes,
            'es_principal' => (bool) $documento->es_principal,
            'created_at' => $documento->created_at,
        ];
    }
}
