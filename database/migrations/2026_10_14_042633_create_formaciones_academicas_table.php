<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'dbreclutamiento.formaciones_academicas',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('candidato_id')
                    ->constrained('dbreclutamiento.candidatos');

                $table->string('institucion', 200);
                $table->string('titulo', 200);

                $table->string('nivel_academico', 100);
                $table->string('area_estudio', 150)->nullable();

                $table->date('fecha_inicio')->nullable();
                $table->date('fecha_fin')->nullable();

                $table->boolean('en_curso')->default(false);
                $table->boolean('finalizado')->default(false);

                $table->text('descripcion')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index('candidato_id');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'dbreclutamiento.formaciones_academicas'
        );
    }
};
