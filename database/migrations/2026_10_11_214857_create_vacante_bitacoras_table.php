<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dbreclutamiento.vacante_bitacoras', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vacante_id')
                ->references('id')
                ->on('dbreclutamiento.vacantes')
                ->noActionOnDelete();

            $table->string('accion', 50);
            $table->text('descripcion')->nullable();

            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();

            $table->unsignedBigInteger('usuario_id')->nullable();

            $table->timestamps();

            $table->index(['vacante_id', 'created_at']);
            $table->index('accion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dbreclutamiento.vacante_bitacoras');
    }
};
