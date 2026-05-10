<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: tabla "usuarios"
 * Guarda los datos de cada cazador registrado en La Caza.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $tabla) {
            $tabla->id('idUsuario');
            $tabla->string('nombre', 50);
            $tabla->string('apellidos', 100);
            $tabla->string('email', 100)->unique();
            $tabla->string('contrasenaHash');   // contraseña encriptada
            $tabla->string('avatar', 5)->default('🤠'); // emoji que representa al cazador
            $tabla->date('fechaNac')->nullable();
            $tabla->integer('xp')->default(0);  // puntos de experiencia totales
            $tabla->enum('plan', ['recluta', 'cazador', 'corporativo'])->default('recluta');
            $tabla->timestamps(); // created_at y updated_at automáticos
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
