<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dbreclutamiento.postulaciones', function (Blueprint $table) {
            $table->date('fecha_ingreso')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('dbreclutamiento.postulaciones', function (Blueprint $table) {
            $table->dropColumn('fecha_ingreso');
        });
    }
};
