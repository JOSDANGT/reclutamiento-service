<?php

use App\Http\Controllers\Api\auth\CandidatoController;
use App\Http\Controllers\Api\auth\CandidatoHabilidadController;
use App\Http\Controllers\Api\auth\ExperienciaLaboralController;
use App\Http\Controllers\Api\auth\FormacionAcademicaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\auth\VacanteController;
use App\Http\Controllers\Api\auth\RequisitoController;
use App\Http\Controllers\Api\auth\TipoRequisitoController;

/*
|--------------------------------------------------------------------------
| VACANTES
|--------------------------------------------------------------------------
*/

Route::prefix('vacantes')->group(function () {

    Route::get('/', [
        VacanteController::class, 'index'
    ])->middleware('identity.permission:Ver vacantes');

    Route::get('/{vacante}', [
        VacanteController::class, 'show'
    ])->middleware('identity.permission:Ver vacantes');

    Route::post('/', [
        VacanteController::class, 'store'
    ])->middleware('identity.permission:Crear vacantes');

    Route::match(['put', 'patch'], '/{vacante}', [
        VacanteController::class, 'update'
    ])->middleware('identity.permission:Editar vacantes');

    Route::patch('/{vacante}/estado', [
        VacanteController::class, 'cambiarEstado'
    ])->middleware('identity.permission:Cambiar estado vacantes');

    Route::delete('/{vacante}', [
        VacanteController::class, 'destroy'
    ])->middleware('identity.permission:Eliminar vacantes');
});

/*
|--------------------------------------------------------------------------
| TIPOS DE REQUISITOS
|--------------------------------------------------------------------------
*/

Route::prefix('tipos-requisitos')->group(function () {

    Route::get('/', [
        TipoRequisitoController::class, 'index'
    ])->middleware('identity.permission:Ver tipos de requisitos');

    Route::get('/{tipoRequisito}', [
        TipoRequisitoController::class, 'show'
    ])->middleware('identity.permission:Ver tipos de requisitos');

    Route::post('/', [
        TipoRequisitoController::class, 'store'
    ])->middleware('identity.permission:Crear tipos de requisitos');

    Route::match(['put', 'patch'], '/{tipoRequisito}', [
        TipoRequisitoController::class, 'update'
    ])->middleware('identity.permission:Editar tipos de requisitos');

    Route::delete('/{tipoRequisito}', [
        TipoRequisitoController::class, 'destroy'
    ])->middleware('identity.permission:Eliminar tipos de requisitos');
});

/*
|--------------------------------------------------------------------------
| REQUISITOS
|--------------------------------------------------------------------------
*/

Route::prefix('requisitos')->group(function () {

    Route::get('/', [
        RequisitoController::class, 'index'
    ])->middleware('identity.permission:Ver requisitos');

    Route::get('/{requisito}', [
        RequisitoController::class, 'show'
    ])->middleware('identity.permission:Ver requisitos');

    Route::post('/', [
        RequisitoController::class, 'store'
    ])->middleware('identity.permission:Crear requisitos');

    Route::match(['put', 'patch'], '/{requisito}', [
        RequisitoController::class, 'update'
    ])->middleware('identity.permission:Editar requisitos');

    Route::delete('/{requisito}', [
        RequisitoController::class, 'destroy'
    ])->middleware('identity.permission:Eliminar requisitos');
});

/*
|--------------------------------------------------------------------------
| CANDIDATOS
|--------------------------------------------------------------------------
*/

Route::prefix('candidatos')->group(function () {

    // Perfil del usuario autenticado
    Route::post('perfil', [
        CandidatoController::class, 'store'
    ])->middleware('identity.permission:Crear perfil candidato');

    Route::get('mi-perfil', [
        CandidatoController::class, 'miPerfil'
    ])->middleware('identity.permission:Ver perfil candidato');

    Route::match(['put', 'patch'], 'mi-perfil', [
        CandidatoController::class, 'update'
    ])->middleware('identity.permission:Editar perfil candidato');

    // Administración de candidatos
    Route::get('/', [
        CandidatoController::class, 'index'
    ])->middleware('identity.permission:Ver candidatos');

    Route::get('{candidato}', [
        CandidatoController::class, 'show'
    ])->whereNumber('candidato')
        ->middleware('identity.permission:Ver candidatos');

});

Route::prefix('candidatos/mi-perfil/habilidades')->group(function () {

    Route::get('/', [
        CandidatoHabilidadController::class, 'index'
    ])->middleware('identity.permission:Ver perfil candidato');

    Route::post('/', [
        CandidatoHabilidadController::class, 'store'
    ])->middleware('identity.permission:Gestionar habilidades candidato');

    Route::patch('{habilidad}', [
        CandidatoHabilidadController::class, 'update'
    ])->whereNumber('habilidad')
        ->middleware('identity.permission:Gestionar habilidades candidato');

    Route::delete('{habilidad}', [
        CandidatoHabilidadController::class, 'destroy'
    ])->whereNumber('habilidad')
        ->middleware('identity.permission:Gestionar habilidades candidato');
});


Route::prefix('candidatos/mi-perfil/experiencias')
    ->group(function () {

        Route::get('/', [
            ExperienciaLaboralController::class, 'index'
        ])->middleware('identity.permission:Ver perfil candidato');

        Route::get('{experiencia}', [
            ExperienciaLaboralController::class, 'show'
        ])->whereNumber('experiencia')
            ->middleware('identity.permission:Ver perfil candidato');

        Route::post('/', [
            ExperienciaLaboralController::class, 'store'
        ])->middleware('identity.permission:Gestionar experiencias laborales');

        Route::patch('{experiencia}', [
            ExperienciaLaboralController::class, 'update'
        ])->whereNumber('experiencia')
            ->middleware('identity.permission:Gestionar experiencias laborales');

        Route::delete('{experiencia}', [
            ExperienciaLaboralController::class, 'destroy'
        ])->whereNumber('experiencia')
            ->middleware('identity.permission:Gestionar experiencias laborales');
    });


Route::prefix('candidatos/mi-perfil/formaciones')
    ->group(function () {

        Route::get('/', [
            FormacionAcademicaController::class, 'index'
        ])->middleware('identity.permission:Ver perfil candidato');

        Route::post('/', [
            FormacionAcademicaController::class, 'store'
        ])->middleware('identity.permission:Gestionar formaciones académicas');

        Route::get('{formacion}', [
            FormacionAcademicaController::class, 'show'
        ])->whereNumber('formacion')
            ->middleware('identity.permission:Ver perfil candidato');

        Route::patch('{formacion}', [
            FormacionAcademicaController::class, 'update'
        ])->whereNumber('formacion')
            ->middleware('identity.permission:Gestionar formaciones académicas');

        Route::delete('{formacion}', [
            FormacionAcademicaController::class, 'destroy'
        ])->whereNumber('formacion')
            ->middleware('identity.permission:Gestionar formaciones académicas');
    });
