<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dbreclutamiento.vacantes', function (Blueprint $table) {
            $table->id();

            $table->string('titulo', 150);
            $table->text('descripcion')->nullable();

            $table->unsignedInteger('cantidad_plazas')->default(1);

            $table->decimal('salario_minimo', 12, 2)->nullable();
            $table->decimal('salario_maximo', 12, 2)->nullable();

            $table->string('modalidad', 30);
            $table->string('tipo_contrato', 30);
            $table->string('nivel_experiencia', 30);

            $table->string('estado', 20)->default('BORRADOR');

            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_limite')->nullable();

            $table->unsignedBigInteger('creado_por')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
            $table->index('modalidad');
            $table->index(['estado', 'fecha_limite']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dbreclutamiento.vacantes');
    }
};
