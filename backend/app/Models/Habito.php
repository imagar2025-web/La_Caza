<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Habito
 * Cada hábito pertenece a una sala y tiene una puntuación asignada.
 */
class Habito extends Model
{
    protected $table = 'habitos';
    protected $primaryKey = 'idHabito';

    protected $fillable = [
        'idSala',
        'nombre',
        'descripcion',
        'icono',
        'puntos',
        'activo',
    ];

    protected $casts = [
        'puntos' => 'float',
        'activo' => 'boolean',
    ];

    // ─────────── RELACIONES ───────────

    /**
     * Sala a la que pertenece este hábito.
     */
    public function sala(): BelongsTo
    {
        return $this->belongsTo(Sala::class, 'idSala', 'idSala');
    }

    /**
     * Veces que se ha registrado este hábito (por todos los usuarios).
     */
    public function registros(): HasMany
    {
        return $this->hasMany(RegistroDiario::class, 'idHabito', 'idHabito');
    }
}
