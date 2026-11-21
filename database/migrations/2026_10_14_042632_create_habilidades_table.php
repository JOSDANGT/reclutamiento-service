<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dbreclutamiento.habilidades', function (Blueprint $table) {
            $table->id();

            $table->string('nombre', 150)->unique();
            $table->foreignId('categoria_id')
                ->constrained('dbreclutamiento.habilidad_categorias');
            $table->text('descripcion')->nullable();

            $table->boolean('estado')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('categoria_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dbreclutamiento.habilidades');
    }
};
