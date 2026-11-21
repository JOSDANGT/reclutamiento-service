<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dbreclutamiento.candidatos', function (Blueprint $table) {
            $table->id();

            // Referencia lógica a Identity Service
            $table->unsignedBigInteger('user_id')->unique();

            // Perfil profesional
            $table->string('titulo_profesional', 150)->nullable();
            $table->text('resumen_profesional')->nullable();
            $table->unsignedSmallInteger('anios_experiencia')->nullable();
            $table->string('nivel_academico', 100)->nullable();

            // Contacto y localización general
            $table->string('telefono_contacto', 25)->nullable();
            $table->string('pais', 100)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->string('municipio', 100)->nullable();

            // Información laboral
            $table->string('disponibilidad', 30)->nullable();
            $table->decimal('pretension_salarial', 12, 2)->nullable();

            // Enlaces profesionales
            $table->string('linkedin_url', 500)->nullable();
            $table->string('github_url', 500)->nullable();
            $table->string('portafolio_url', 500)->nullable();

            $table->boolean('estado')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('titulo_profesional');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dbreclutamiento.candidatos');
    }
};
