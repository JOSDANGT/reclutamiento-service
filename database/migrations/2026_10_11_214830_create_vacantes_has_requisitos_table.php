<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dbreclutamiento.vacantes_has_requisitos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('vacante_id')
                ->references('id')
                ->on('dbreclutamiento.vacantes')
                ->noActionOnDelete();

            $table->foreignId('requisito_id')
                ->references('id')
                ->on('dbreclutamiento.requisitos')
                ->noActionOnDelete();

            $table->boolean('obligatorio')->default(true);
            $table->unsignedInteger('orden')->default(0);

            $table->timestamps();

            $table->unique(
                ['vacante_id', 'requisito_id'],
                'uq_vacantes_has_requisitos'
            );

            $table->index('requisito_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dbreclutamiento.vacantes_has_requisitos');
    }
};
