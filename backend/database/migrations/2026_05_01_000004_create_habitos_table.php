<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: tabla "habitos"
 * Cada sala tiene sus propios hábitos definidos por el anfitrión.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habitos', function (Blueprint $tabla) {
            $tabla->id('idHabito');
            $tabla->foreignId('idSala')->constrained('salas', 'idSala')->onDelete('cascade');
            $tabla->string('nombre', 50);
            $tabla->string('descripcion', 100)->nullable();
            $tabla->string('icono', 5)->default('⭐'); // emoji
            $tabla->decimal('puntos', 3, 1); // entre 1.0 y 10.0 (con un decimal)
            $tabla->boolean('activo')->default(true);
            $tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habitos');
    }
};
