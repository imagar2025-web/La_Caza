<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as ModeloAutenticable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

/**
 * Modelo Usuario
 * Representa la tabla "usuarios" y define sus relaciones con otras tablas.
 */
class Usuario extends ModeloAutenticable
{
    use HasApiTokens; // permite generar tokens para login

    // Le decimos a Laravel cómo se llama nuestra tabla y nuestra clave primaria,
    // porque por defecto buscaría "users" e "id"
    protected $table = 'usuarios';
    protected $primaryKey = 'idUsuario';

    // Campos que se pueden rellenar masivamente con create() o fill()
    protected $fillable = [
        'nombre',
        'apellidos',
        'email',
        'contrasenaHash',
        'avatar',
        'fechaNac',
        'xp',
        'plan',
    ];

    // Campos que NUNCA se devuelven en respuestas JSON (por seguridad)
    protected $hidden = [
        'contrasenaHash',
    ];

    // Le decimos a Laravel qué campo guarda la contraseña (para el login)
    public function getAuthPassword()
    {
        return $this->contrasenaHash;
    }

    // ─────────── RELACIONES ───────────

    /**
     * Salas en las que participa este usuario (relación N:M).
     */
    public function salas(): BelongsToMany
    {
        return $this->belongsToMany(
            Sala::class,
            'usuarios_salas',
            'idUsuario',
            'idSala'
        )->withPivot('fechaUnion', 'puntosAcumulados', 'rachaActual');
    }

    /**
     * Salas que este usuario ha creado (es anfitrión).
     */
    public function salasComoAnfitrion(): HasMany
    {
        return $this->hasMany(Sala::class, 'idAnfitrion', 'idUsuario');
    }

    /**
     * Todos los registros diarios de hábitos del usuario.
     */
    public function registros(): HasMany
    {
        return $this->hasMany(RegistroDiario::class, 'idUsuario', 'idUsuario');
    }

    /**
     * Logros desbloqueados por este usuario.
     */
    public function logros(): BelongsToMany
    {
        return $this->belongsToMany(
            Logro::class,
            'usuarios_logros',
            'idUsuario',
            'idLogro'
        )->withPivot('fechaDesbloqueo', 'progreso');
    }
}
