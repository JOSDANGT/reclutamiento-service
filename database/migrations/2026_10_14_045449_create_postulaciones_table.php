<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('dbreclutamiento.postulaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('candidato_id')
                ->constrained('dbreclutamiento.candidatos');

            $table->foreignId('vacante_id')
                ->constrained('dbreclutamiento.vacantes');

            $table->foreignId('estado_id')
                ->constrained('dbreclutamiento.postulacion_estados');

            $table->dateTime('fecha_postulacion')->useCurrent();

            $table->text('carta_presentacion')->nullable();
            $table->text('observaciones')->nullable();

            $table->unsignedBigInteger('revisado_por')->nullable();
            $table->dateTime('fecha_revision')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['candidato_id', 'vacante_id'],
                'uq_postulacion_candidato_vacante'
            );

            $table->index(['vacante_id', 'estado_id']);
            $table->index('fecha_postulacion');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('dbreclutamiento.postulaciones');
    }
};
