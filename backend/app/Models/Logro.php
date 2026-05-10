<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Modelo Logro
 * Catálogo de medallas que pueden desbloquear los usuarios.
 */
class Logro extends Model
{
    protected $table = 'logros';
    protected $primaryKey = 'idLogro';
    public $timestamps = false; // este catálogo no necesita created_at/updated_at

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'icono',
        'tipo',
        'objetivo',
        'xpRecompensa',
    ];

    /**
     * Usuarios que han desbloqueado este logro.
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            Usuario::class,
            'usuarios_logros',
            'idLogro',
            'idUsuario'
        )->withPivot('fechaDesbloqueo', 'progreso');
    }
}
