<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('sqlsrv')
            ->create('dbreclutamiento.postulacion_requisitos', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('postulacion_id');
                $table->unsignedBigInteger('requisito_id');

                $table->boolean('cumple')->nullable();
                $table->string('observacion', 500)->nullable();

                $table->unsignedBigInteger('evaluado_por')->nullable();
                $table->dateTime('evaluado_at')->nullable();

                $table->timestamps();

                $table->unique([
                    'postulacion_id',
                    'requisito_id',
                ], 'postulacion_requisito_unique');
            });
    }

    public function down(): void
    {
        Schema::connection('sqlsrv')
            ->dropIfExists('dbreclutamiento.postulacion_requisitos');
    }
};
