<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dbreclutamiento.requisitos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tipo_requisito_id')
                ->references('id')
                ->on('dbreclutamiento.tipos_requisitos')
                ->noActionOnDelete();

            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->boolean('estado')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('tipo_requisito_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dbreclutamiento.requisitos');
    }
};
