<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dbreclutamiento.postulacion_requisitos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('postulacion_id')
                ->constrained('dbreclutamiento.postulaciones');

            $table->foreignId('requisito_id')
                ->constrained('dbreclutamiento.vacantes_has_requisitos');

            $table->boolean('cumple')->nullable();
            $table->string('observacion', 500)->nullable();

            $table->foreignId('evaluado_por')
                ->nullable();

            $table->dateTime('evaluado_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['postulacion_id', 'requisito_id'],
                'postulacion_requisito_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dbreclutamiento.postulacion_requisitos');
    }
};
