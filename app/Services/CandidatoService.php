<?php

namespace App\Services;

use App\Models\Candidato;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CandidatoService
{
    public function crear(array $datos, int $usuarioId): Candidato
    {
        return DB::connection('sqlsrv')->transaction(
            function () use ($datos, $usuarioId) {

                $existe = Candidato::withTrashed()
                    ->where('user_id', $usuarioId)
                    ->exists();

                if ($existe) {
                    throw ValidationException::withMessages([
                        'perfil' => [
                            'El usuario ya tiene un perfil profesional registrado.'
                        ],
                    ]);
                }

                $candidato = Candidato::create([
                    ...$datos,
                    'user_id' => $usuarioId,
                    'estado' => true,
                ]);

                $candidato->bitacoras()->create([
                    'accion' => 'CREACION_PERFIL',
                    'descripcion' => 'Se registró el perfil profesional.',
                    'datos_anteriores' => null,
                    'datos_nuevos' => $candidato->getAttributes(),
                    'usuario_id' => $usuarioId,
                ]);

                return $candidato->fresh();
            }
        );
    }

    public function miPerfil(int $usuarioId): ?Candidato
    {
        return Candidato::with([
            'habilidades',
            'experienciasLaborales',
            'formacionesAcademicas',
            'documentos',
        ])
            ->where('user_id', $usuarioId)
            ->first();
    }

    public function actualizar(
        array $datos,
        int $usuarioId
    ): Candidato {
        return DB::connection('sqlsrv')->transaction(
            function () use ($datos, $usuarioId) {

                $candidato = Candidato::where('user_id', $usuarioId)
                    ->firstOrFail();

                $anteriores = $candidato->only(
                    array_keys($datos)
                );

                $candidato->fill($datos);

                $cambios = $candidato->getDirty();

                if (!empty($cambios)) {
                    $candidato->save();

                    $candidato->bitacoras()->create([
                        'accion' => 'ACTUALIZACION_PERFIL',
                        'descripcion' => 'Se actualizó el perfil profesional.',
                        'datos_anteriores' => array_intersect_key(
                            $anteriores,
                            $cambios
                        ),
                        'datos_nuevos' => $cambios,
                        'usuario_id' => $usuarioId,
                    ]);
                }

                return $candidato->fresh([
                    'habilidades',
                    'experienciasLaborales',
                    'formacionesAcademicas',
                    'documentos',
                ]);
            }
        );
    }

    public function listar(array $filtros = [])
    {
        return Candidato::query()
            ->when(
                isset($filtros['estado']),
                fn ($query) => $query->where(
                    'estado',
                    $filtros['estado']
                )
            )
            ->when(
                !empty($filtros['search']),
                function ($query) use ($filtros) {
                    $query->where(
                        'titulo_profesional',
                        'like',
                        '%' . $filtros['search'] . '%'
                    );
                }
            )
            ->withCount('postulaciones')
            ->orderByDesc('id')
            ->paginate(15);
    }

    public function obtener(int $id): Candidato
    {
        return Candidato::with([
            'habilidades',
            'experienciasLaborales',
            'formacionesAcademicas',
            'documentos',
            'postulaciones.estado',
            'postulaciones.vacante',
        ])->findOrFail($id);
    }
}
