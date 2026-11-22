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

        Schema::create('dbreclutamiento.users', function (Blueprint $table) {
            $table->id();

            // Identificación del usuario
            $table->string('usuario_dominio', 100)
                ->nullable()
                ->unique();
            $table->string('puesto', 150)->nullable();

            // Información personal
            $table->string('primer_nombre', 100);
            $table->string('segundo_nombre', 100)->nullable();
            $table->string('tercer_nombre', 100)->nullable();

            $table->string('primer_apellido', 100);
            $table->string('segundo_apellido', 100)->nullable();
            $table->string('apellido_casada', 100)->nullable();

            // Contacto
            $table->string('email', 150)->unique();
            $table->string('telefono', 25)->nullable();

            // Autenticación
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Estado y seguimiento
            $table->boolean('estado')->default(true);
            $table->dateTime('ultimo_acceso_at')->nullable();

            // Laravel
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });


        Schema::create('dbreclutamiento.password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('dbreclutamiento.sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dbreclutamiento.users');
        Schema::dropIfExists('dbreclutamiento.password_reset_tokens');
        Schema::dropIfExists('dbreclutamiento.sessions');
    }
};
