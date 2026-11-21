<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'dbreclutamiento.candidatos_has_habilidades',
            function (Blueprint $table) {

                $table->foreignId('candidato_id')
                    ->constrained('dbreclutamiento.candidatos');

                $table->foreignId('habilidad_id')
                    ->constrained('dbreclutamiento.habilidades');

                $table->foreignId('nivel_habilidad_id')->constrained('dbreclutamiento.nivel_habilidad');

                $table->unsignedSmallInteger('anios_experiencia')->nullable();

                $table->timestamps();

                $table->primary([
                    'candidato_id',
                    'habilidad_id'
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'dbreclutamiento.candidatos_has_habilidades'
        );
    }
};
