<?php

use App\Http\Controllers\Api\auth\AuthController;
use App\Http\Controllers\Api\CandidatoController;
use App\Http\Controllers\Api\CandidatoHabilidadController;
use App\Http\Controllers\Api\ExperienciaLaboralController;
use App\Http\Controllers\Api\FormacionAcademicaController;
use App\Http\Controllers\Api\OportunidadController;
use App\Http\Controllers\Api\PostulacionController;
use App\Http\Controllers\Api\PostulacionRequisitoController;
use App\Http\Controllers\Api\PuestoController;
use App\Http\Controllers\Api\UnidadController;
use App\Http\Controllers\Api\VacanteController;
use App\Http\Controllers\Api\RequisitoController;
use App\Http\Controllers\Api\TipoRequisitoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AUTENTICACIÓN
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

});

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | VACANTES
    |--------------------------------------------------------------------------
    */

    Route::patch('vacantes/estado/{vacante}', [
        VacanteController::class, 'cambiarEstado'
    ])->middleware('identity.permission:Cambiar estado vacantes');

    Route::apiResource('vacantes', VacanteController::class)
        ->middlewareFor(['index', 'show'], 'identity.permission:Ver vacantes')
        ->middlewareFor('store', 'identity.permission:Crear vacantes')
        ->middlewareFor('update', 'identity.permission:Editar vacantes')
        ->middlewareFor('destroy', 'identity.permission:Eliminar vacantes');

    /*
    |--------------------------------------------------------------------------
    | TIPOS DE REQUISITOS
    |--------------------------------------------------------------------------
    */

    Route::apiResource('tipos-requisitos', TipoRequisitoController::class)
        ->parameters(['tipos-requisitos' => 'tipoRequisito'])
        ->middlewareFor(['index', 'show'], 'identity.permission:Ver tipos de requisitos')
        ->middlewareFor('store', 'identity.permission:Crear tipos de requisitos')
        ->middlewareFor('update', 'identity.permission:Editar tipos de requisitos')
        ->middlewareFor('destroy', 'identity.permission:Eliminar tipos de requisitos');

    /*
    |--------------------------------------------------------------------------
    | REQUISITOS
    |--------------------------------------------------------------------------
    */

    Route::apiResource('requisitos', RequisitoController::class)
        ->middlewareFor(['index', 'show'], 'identity.permission:Ver requisitos')
        ->middlewareFor('store', 'identity.permission:Crear requisitos')
        ->middlewareFor('update', 'identity.permission:Editar requisitos')
        ->middlewareFor('destroy', 'identity.permission:Eliminar requisitos');

    /*
    |--------------------------------------------------------------------------
    | CANDIDATOS
    |--------------------------------------------------------------------------
    */

    // Perfil del usuario autenticado
    Route::prefix('candidatos')->group(function () {

        Route::post('perfil', [
            CandidatoController::class, 'store'
        ])->middleware('identity.permission:Crear perfil candidato');

        Route::get('mi-perfil', [
            CandidatoController::class, 'miPerfil'
        ])->middleware('identity.permission:Ver perfil candidato');

        Route::match(['put', 'patch'], 'mi-perfil', [
            CandidatoController::class, 'update'
        ])->middleware('identity.permission:Editar perfil candidato');

    });

    // Administración de candidatos
    Route::apiResource('candidatos', CandidatoController::class)
        ->only(['index', 'show'])
        ->where(['candidato' => '[0-9]+'])
        ->middlewareFor(['index', 'show'], 'identity.permission:Ver candidatos');

    /*
    |--------------------------------------------------------------------------
    | HABILIDADES DEL CANDIDATO
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'candidatos/mi-perfil/habilidades',
        CandidatoHabilidadController::class
    )
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['habilidades' => 'habilidad'])
        ->where(['habilidad' => '[0-9]+'])
        ->middlewareFor('index', 'identity.permission:Ver perfil candidato')
        ->middlewareFor(
            ['store', 'update', 'destroy'],
            'identity.permission:Gestionar habilidades candidato'
        );

    /*
    |--------------------------------------------------------------------------
    | EXPERIENCIAS LABORALES
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'candidatos/mi-perfil/experiencias',
        ExperienciaLaboralController::class
    )
        ->parameters(['experiencias' => 'experiencia'])
        ->where(['experiencia' => '[0-9]+'])
        ->middlewareFor(['index', 'show'], 'identity.permission:Ver perfil candidato')
        ->middlewareFor(
            ['store', 'update', 'destroy'],
            'identity.permission:Gestionar experiencias laborales'
        );

    /*
    |--------------------------------------------------------------------------
    | FORMACIONES ACADÉMICAS
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'candidatos/mi-perfil/formaciones',
        FormacionAcademicaController::class
    )
        ->parameters(['formaciones' => 'formacion'])
        ->where(['formacion' => '[0-9]+'])
        ->middlewareFor(['index', 'show'], 'identity.permission:Ver perfil candidato')
        ->middlewareFor(
            ['store', 'update', 'destroy'],
            'identity.permission:Gestionar formaciones académicas'
        );

    /*
    |--------------------------------------------------------------------------
    | UNIDADES
    |--------------------------------------------------------------------------
    */

    Route::patch('unidades/estado/{unidad}', [
        UnidadController::class, 'cambiarEstado'
    ])->middleware('identity.permission:Editar unidades');

    Route::apiResource('unidades', UnidadController::class)
        ->except(['destroy'])
        ->middlewareFor(['index', 'show'], 'identity.permission:Ver unidades')
        ->middlewareFor('store', 'identity.permission:Crear unidades')
        ->middlewareFor('update', 'identity.permission:Editar unidades');



    Route::get(
        'postulaciones/requisitos/{postulacion}',
        [PostulacionRequisitoController::class, 'index']
    );

    Route::post(
        'postulaciones/requisitos/{postulacion}',
        [PostulacionRequisitoController::class, 'guardar']
    )->middleware('identity.permission:Gestionar postulaciones');


    Route::get(
        'puestos',
        [PuestoController::class, 'index']
    );

    Route::get(
        'puestos/requisitos/{puesto}',
        [PuestoController::class, 'requisitos']
    );

    Route::get('postulaciones', [PostulacionController::class, 'index']
    )->middleware('identity.permission:Ver postulaciones');

    Route::get('postulaciones/{postulacion}', [PostulacionController::class, 'show']
    )->middleware('identity.permission:Ver postulaciones');

    Route::post('postulaciones', [PostulacionController::class, 'store']
    )->middleware('identity.permission:Crear postulaciones');

    Route::get('oportunidades', [OportunidadController::class, 'index']
    )->middleware('identity.permission:Ver oportunidades laborales');

    Route::get('oportunidades/{vacante}', [OportunidadController::class, 'show']
    )->middleware('identity.permission:Ver oportunidades laborales');

});
