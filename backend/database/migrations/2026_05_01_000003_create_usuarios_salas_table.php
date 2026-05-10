<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: tabla intermedia "usuarios_salas"
 * Relaciona qué usuarios participan en qué salas.
 * Un usuario puede estar en muchas salas y una sala tiene muchos usuarios (relación N:M).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios_salas', function (Blueprint $tabla) {
            $tabla->foreignId('idUsuario')->constrained('usuarios', 'idUsuario')->onDelete('cascade');
            $tabla->foreignId('idSala')->constrained('salas', 'idSala')->onDelete('cascade');
            $tabla->date('fechaUnion');
            $tabla->integer('puntosAcumulados')->default(0);
            $tabla->integer('rachaActual')->default(0);

            // Clave primaria compuesta: un usuario solo puede estar una vez en cada sala
            $tabla->primary(['idUsuario', 'idSala']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios_salas');
    }
};
