<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tablaUsuarios = (new User())->getTable();

        Schema::create('dbreclutamiento.agendas', function (Blueprint $table) use ($tablaUsuarios) {
            $table->id();

            $table->foreignId('postulacion_id')
                ->constrained('dbreclutamiento.postulaciones');

            $table->string('tipo', 50);

            // 1 = Recursos Humanos, 2 = Jefe inmediato, 3 = Entrevista técnica.
            $table->tinyInteger('etapa');

            // Usuario asignado para realizar la entrevista.
            $table->foreignId('usuario_id')
                ->constrained($tablaUsuarios);

            $table->dateTime('fecha_hora');
            $table->string('modalidad', 20);
            $table->string('lugar_enlace', 500)->nullable();

            $table->string('estado', 20)->default('PROGRAMADA');
            $table->string('resultado', 20)->nullable();
            $table->text('observaciones')->nullable();
            $table->text('motivo_cancelacion')->nullable();

            $table->boolean('continuidad_autorizada')->default(false);
            $table->text('justificacion_continuidad')->nullable();
            $table->foreignId('continuidad_autorizada_por')
                ->nullable()
                ->constrained($tablaUsuarios);
            $table->dateTime('continuidad_autorizada_en')->nullable();

            $table->foreignId('creado_por')
                ->nullable()
                ->constrained($tablaUsuarios);

            $table->foreignId('gestionado_por')
                ->nullable()
                ->constrained($tablaUsuarios);

            $table->timestamps();

            $table->index(['postulacion_id', 'etapa']);
            $table->index(['usuario_id', 'fecha_hora']);
            $table->index(['estado', 'fecha_hora']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dbreclutamiento.agendas');
    }
};
