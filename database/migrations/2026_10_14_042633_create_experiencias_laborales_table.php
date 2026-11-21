<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'dbreclutamiento.experiencias_laborales',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('candidato_id')
                    ->constrained('dbreclutamiento.candidatos');

                $table->string('empresa', 200);
                $table->string('puesto', 150);

                $table->string('tipo_empleo', 50)->nullable();
                $table->string('ubicacion', 200)->nullable();

                $table->date('fecha_inicio');
                $table->date('fecha_fin')->nullable();

                $table->boolean('trabajo_actual')->default(false);

                $table->text('descripcion')->nullable();
                $table->text('logros')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index('candidato_id');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'dbreclutamiento.experiencias_laborales'
        );
    }
};
