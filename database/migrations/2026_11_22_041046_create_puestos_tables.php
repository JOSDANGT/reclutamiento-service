
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dbreclutamiento.puestos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('unidad_id')
                ->constrained('dbreclutamiento.unidades');

            $table->string('nombre', 150);
            $table->string('descripcion', 500)->nullable();
            $table->boolean('estado')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['unidad_id', 'nombre']);
        });

        Schema::create('dbreclutamiento.puestos_has_requisitos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('puesto_id');
            $table->unsignedBigInteger('requisito_id');
            $table->boolean('obligatorio')->default(true);
            $table->integer('orden')->default(1);
            $table->timestamps();

            $table->unique(
                ['puesto_id', 'requisito_id'],
                'puestos_requisitos_unique'
            );

            $table->foreign('puesto_id')
                ->references('id')
                ->on('dbreclutamiento.puestos');

            $table->foreign('requisito_id')
                ->references('id')
                ->on('dbreclutamiento.requisitos');
        });

        Schema::table('dbreclutamiento.vacantes', function (Blueprint $table) {
            $table->unsignedBigInteger('puesto_id')->nullable();

            $table->foreign('puesto_id')
                ->references('id')
                ->on('dbreclutamiento.puestos');
        });

        Schema::table('dbreclutamiento.users', function (Blueprint $table) {
            $table->unsignedBigInteger('puesto_id')->nullable();

            $table->foreign('puesto_id')
                ->references('id')
                ->on('dbreclutamiento.puestos');
        });
    }

    public function down(): void
    {
        Schema::table('dbreclutamiento.users', function (Blueprint $table) {
            $table->dropForeign(['puesto_id']);
            $table->dropColumn('puesto_id');
        });
        Schema::table('dbreclutamiento.vacantes', function (Blueprint $table) {
            $table->dropForeign(['puesto_id']);
            $table->dropColumn('puesto_id');
        });

        Schema::dropIfExists('dbreclutamiento.puestos_has_requisitos');

        Schema::dropIfExists('dbreclutamiento.puestos');
    }
};
