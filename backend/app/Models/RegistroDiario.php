<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo RegistroDiario
 * Cada vez que un usuario marca un hábito como cumplido, se crea un registro aquí.
 */
class RegistroDiario extends Model
{
    protected $table = 'registro_diario';
    protected $primaryKey = 'idRegistro';

    protected $fillable = [
        'idUsuario',
        'idSala',
        'idHabito',
        'fecha',
        'puntosObtenidos',
    ];

    protected $casts = [
        'fecha' => 'date',
        'puntosObtenidos' => 'float',
    ];

    // ─────────── RELACIONES ───────────

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
    }

    public function sala(): BelongsTo
    {
        return $this->belongsTo(Sala::class, 'idSala', 'idSala');
    }

    public function habito(): BelongsTo
    {
        return $this->belongsTo(Habito::class, 'idHabito', 'idHabito');
    }
}
