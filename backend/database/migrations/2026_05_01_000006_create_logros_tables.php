<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: tablas "logros" y "usuarios_logros"
 * - "logros" guarda el catálogo de medallas posibles.
 * - "usuarios_logros" guarda qué medallas ha desbloqueado cada usuario.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Catálogo de logros disponibles
        Schema::create('logros', function (Blueprint $tabla) {
            $tabla->id('idLogro');
            $tabla->string('codigo', 30)->unique(); // identificador único legible
            $tabla->string('nombre', 50);
            $tabla->string('descripcion', 150);
            $tabla->string('icono', 5)->default('🏆');
            $tabla->enum('tipo', ['racha', 'puntos', 'social', 'fundador'])->default('puntos');
            $tabla->integer('objetivo'); // número a alcanzar
            $tabla->integer('xpRecompensa')->default(25);
        });

        // Logros desbloqueados por cada usuario
        Schema::create('usuarios_logros', function (Blueprint $tabla) {
            $tabla->foreignId('idUsuario')->constrained('usuarios', 'idUsuario')->onDelete('cascade');
            $tabla->foreignId('idLogro')->constrained('logros', 'idLogro')->onDelete('cascade');
            $tabla->date('fechaDesbloqueo');
            $tabla->integer('progreso')->default(0);

            $tabla->primary(['idUsuario', 'idLogro']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios_logros');
        Schema::dropIfExists('logros');
    }
};
