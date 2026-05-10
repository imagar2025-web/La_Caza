<?php

namespace App\Http\Controllers;

use App\Models\Logro;
use Illuminate\Http\Request;

/**
 * Controlador de logros.
 * Lista los logros disponibles y los que ha desbloqueado el usuario.
 */
class LogroController extends Controller
{
    /**
     * Devuelve TODOS los logros del catálogo, marcando cuáles ha desbloqueado el usuario.
     * GET /api/logros
     */
    public function listar(Request $peticion)
    {
        $usuario = $peticion->user();

        // Cogemos todos los logros del catálogo
        $todosLosLogros = Logro::all();

        // Cogemos los IDs de los logros que el usuario ya ha desbloqueado
        $idsDesbloqueados = $usuario->logros()->pluck('logros.idLogro')->toArray();

        // Para cada logro, añadimos un campo "desbloqueado" (true/false)
        $resultado = $todosLosLogros->map(function ($logro) use ($idsDesbloqueados, $usuario) {
            // Buscamos el progreso actual del usuario en este logro (si lo tiene)
            $relacion = $usuario->logros->firstWhere('idLogro', $logro->idLogro);
            $progreso = $relacion ? $relacion->pivot->progreso : 0;

            return [
                'idLogro'       => $logro->idLogro,
                'codigo'        => $logro->codigo,
                'nombre'        => $logro->nombre,
                'descripcion'   => $logro->descripcion,
                'icono'         => $logro->icono,
                'tipo'          => $logro->tipo,
                'objetivo'      => $logro->objetivo,
                'desbloqueado'  => in_array($logro->idLogro, $idsDesbloqueados),
                'progreso'      => $progreso,
            ];
        });

        return response()->json($resultado);
    }
}
