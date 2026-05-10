<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: tabla "registro_diario"
 * Aquí se guarda cada vez que un usuario marca un hábito como cumplido.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_diario', function (Blueprint $tabla) {
            $tabla->id('idRegistro');
            $tabla->foreignId('idUsuario')->constrained('usuarios', 'idUsuario')->onDelete('cascade');
            $tabla->foreignId('idSala')->constrained('salas', 'idSala')->onDelete('cascade');
            $tabla->foreignId('idHabito')->constrained('habitos', 'idHabito')->onDelete('cascade');
            $tabla->date('fecha');
            $tabla->decimal('puntosObtenidos', 3, 1);
            $tabla->timestamps();

            // Para que un usuario no pueda marcar el mismo hábito dos veces el mismo día
            $tabla->unique(['idUsuario', 'idHabito', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_diario');
    }
};
