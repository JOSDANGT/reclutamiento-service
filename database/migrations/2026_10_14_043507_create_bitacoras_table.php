
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dbreclutamiento.bitacoras', function (Blueprint $table) {
            $table->id();

            // Relación polimórfica
            $table->string('model_type', 100);
            $table->unsignedBigInteger('model_id');

            $table->string('accion', 100);
            $table->text('descripcion')->nullable();

            // Para registrar cambios de cualquier modelo
            $table->text('datos_anteriores')->nullable();
            $table->text('datos_nuevos')->nullable();

            // Usuario autenticado mediante Identity Service
            $table->unsignedBigInteger('usuario_id')->nullable();

            $table->timestamps();

            $table->index(
                ['model_type', 'model_id'],
                'idx_bitacoras_model'
            );

            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dbreclutamiento.bitacoras');
    }
};
