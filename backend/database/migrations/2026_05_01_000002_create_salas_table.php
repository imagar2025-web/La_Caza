<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: tabla "salas"
 * Cada sala es una partida privada donde compiten varios cazadores.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salas', function (Blueprint $tabla) {
            $tabla->id('idSala');
            $tabla->string('nombre', 50);
            $tabla->enum('tematica', [
                'deporte', 'estudio', 'bienestar', 'trabajo', 'custom'
            ])->default('custom');
            $tabla->string('codigoInvitacion', 10)->unique(); // código para que se unan amigos
            $tabla->foreignId('idAnfitrion')
                  ->constrained('usuarios', 'idUsuario')
                  ->onDelete('cascade'); // si se borra el anfitrión, se borra la sala
            $tabla->integer('duracionDias')->default(30);
            $tabla->date('fechaInicio');
            $tabla->date('fechaFin');
            $tabla->integer('maxJugadores')->default(6);
            $tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salas');
    }
};
