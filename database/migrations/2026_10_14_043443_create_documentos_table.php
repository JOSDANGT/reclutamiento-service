
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dbreclutamiento.documentos', function (Blueprint $table) {
            $table->id();

            // Relación polimórfica
            $table->string('model_type', 100);
            $table->unsignedBigInteger('model_id');

            $table->string('tipo_documento', 50);
            $table->string('nombre_original', 255);
            $table->string('ruta', 500);
            $table->string('disco', 50)->default('local');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamanio_bytes')->nullable();

            $table->boolean('es_principal')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['model_type', 'model_id'],
                'idx_documentos_model'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dbreclutamiento.documentos');
    }
};
