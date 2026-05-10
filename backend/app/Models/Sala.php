<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Sala
 * Representa una sala/partida privada donde compiten varios usuarios.
 */
class Sala extends Model
{
    protected $table = 'salas';
    protected $primaryKey = 'idSala';

    protected $fillable = [
        'nombre',
        'tematica',
        'codigoInvitacion',
        'idAnfitrion',
        'duracionDias',
        'fechaInicio',
        'fechaFin',
        'maxJugadores',
    ];

    // Campos que Laravel debe convertir automáticamente
    protected $casts = [
        'fechaInicio' => 'date',
        'fechaFin' => 'date',
    ];

    // ─────────── RELACIONES ───────────

    /**
     * El usuario que creó la sala.
     */
    public function anfitrion(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'idAnfitrion', 'idUsuario');
    }

    /**
     * Usuarios que participan en esta sala.
     */
    public function jugadores(): BelongsToMany
    {
        return $this->belongsToMany(
            Usuario::class,
            'usuarios_salas',
            'idSala',
            'idUsuario'
        )->withPivot('fechaUnion', 'puntosAcumulados', 'rachaActual');
    }

    /**
     * Hábitos que se compiten en esta sala.
     */
    public function habitos(): HasMany
    {
        return $this->hasMany(Habito::class, 'idSala', 'idSala');
    }

    /**
     * Todos los registros diarios de la sala (de todos los usuarios).
     */
    public function registros(): HasMany
    {
        return $this->hasMany(RegistroDiario::class, 'idSala', 'idSala');
    }

    /**
     * Genera un código de invitación aleatorio.
     * Lo usaremos al crear una sala nueva.
     */
    public static function generarCodigoInvitacion(): string
    {
        return strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }
}
