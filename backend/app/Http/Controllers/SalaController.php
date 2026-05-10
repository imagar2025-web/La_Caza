<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use App\Models\Habito;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Controlador de salas.
 * Gestiona crear, listar, unirse y consultar salas privadas.
 */
class SalaController extends Controller
{
    /**
     * Lista las salas en las que participa el usuario logueado.
     * GET /api/salas
     */
    public function listarMisSalas(Request $peticion)
    {
        $usuario = $peticion->user();

        // Cargamos las salas con sus jugadores y hábitos para que el frontend tenga todo
        $salas = $usuario->salas()->with(['jugadores', 'habitos'])->get();

        return response()->json($salas);
    }

    /**
     * Devuelve los datos completos de una sala (incluyendo ranking).
     * GET /api/salas/{idSala}
     */
    public function ver($idSala, Request $peticion)
    {
        $sala = Sala::with(['jugadores', 'habitos', 'anfitrion'])->findOrFail($idSala);

        // Comprobamos que el usuario que pide la información está en la sala
        $usuario = $peticion->user();
        $estaEnLaSala = $sala->jugadores->contains('idUsuario', $usuario->idUsuario);

        if (!$estaEnLaSala) {
            return response()->json(['mensaje' => 'No participas en esta sala'], 403);
        }

        // Calculamos el ranking ordenando los jugadores por puntos (de mayor a menor)
        $ranking = $sala->jugadores->sortByDesc(function ($jugador) {
            return $jugador->pivot->puntosAcumulados;
        })->values();

        return response()->json([
            'sala'    => $sala,
            'ranking' => $ranking,
        ]);
    }

    /**
     * Crea una sala nueva.
     * POST /api/salas
     */
    public function crear(Request $peticion)
    {
        $datos = $peticion->validate([
            'nombre'        => 'required|string|max:50',
            'tematica'      => 'required|in:deporte,estudio,bienestar,trabajo,custom',
            'duracionDias'  => 'required|integer|min:7|max:90',
            'maxJugadores'  => 'required|integer|min:2|max:20',
            'habitos'                => 'required|array|min:1|max:15',
            'habitos.*.nombre'       => 'required|string|max:50',
            'habitos.*.descripcion'  => 'nullable|string|max:100',
            'habitos.*.icono'        => 'required|string|max:5',
            'habitos.*.puntos'       => 'required|numeric|min:1|max:10',
        ]);

        $usuario = $peticion->user();

        // 1) Creamos la sala
        $fechaInicio = Carbon::today();
        $sala = Sala::create([
            'nombre'           => $datos['nombre'],
            'tematica'         => $datos['tematica'],
            'codigoInvitacion' => Sala::generarCodigoInvitacion(),
            'idAnfitrion'      => $usuario->idUsuario,
            'duracionDias'     => $datos['duracionDias'],
            'fechaInicio'      => $fechaInicio,
            'fechaFin'         => $fechaInicio->copy()->addDays($datos['duracionDias']),
            'maxJugadores'     => $datos['maxJugadores'],
        ]);

        // 2) Añadimos al anfitrión como primer jugador
        $sala->jugadores()->attach($usuario->idUsuario, [
            'fechaUnion'        => $fechaInicio,
            'puntosAcumulados'  => 0,
            'rachaActual'       => 0,
        ]);

        // 3) Creamos cada hábito que llegó del frontend
        foreach ($datos['habitos'] as $datosHabito) {
            Habito::create([
                'idSala'      => $sala->idSala,
                'nombre'      => $datosHabito['nombre'],
                'descripcion' => $datosHabito['descripcion'] ?? '',
                'icono'       => $datosHabito['icono'],
                'puntos'      => $datosHabito['puntos'],
                'activo'      => true,
            ]);
        }

        // Cargamos las relaciones para devolver los datos completos
        $sala->load(['jugadores', 'habitos']);

        return response()->json([
            'mensaje' => '¡Sala fundada!',
            'sala'    => $sala,
        ], 201);
    }

    /**
     * Permite que un usuario se una a una sala usando el código de invitación.
     * POST /api/salas/unirse
     */
    public function unirse(Request $peticion)
    {
        $datos = $peticion->validate([
            'codigo' => 'required|string|max:10',
        ]);

        // Buscamos la sala por código (lo pasamos a mayúsculas por si el usuario lo escribe en minúsculas)
        $sala = Sala::where('codigoInvitacion', strtoupper($datos['codigo']))->first();

        if (!$sala) {
            return response()->json(['mensaje' => 'Código no válido'], 404);
        }

        $usuario = $peticion->user();

        // Comprobamos que no esté ya en la sala
        if ($sala->jugadores()->where('usuarios.idUsuario', $usuario->idUsuario)->exists()) {
            return response()->json(['mensaje' => 'Ya estás en esta sala'], 400);
        }

        // Comprobamos que no esté llena
        if ($sala->jugadores()->count() >= $sala->maxJugadores) {
            return response()->json(['mensaje' => 'La sala está llena'], 400);
        }

        // Añadimos al usuario
        $sala->jugadores()->attach($usuario->idUsuario, [
            'fechaUnion'        => Carbon::today(),
            'puntosAcumulados'  => 0,
            'rachaActual'       => 0,
        ]);

        return response()->json([
            'mensaje' => '¡Te has unido a la sala!',
            'sala'    => $sala->load(['jugadores', 'habitos']),
        ]);
    }
}
