<?php

namespace App\Http\Controllers;

use App\Models\Habito;
use App\Models\RegistroDiario;
use App\Models\Sala;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Controlador del registro diario de hábitos.
 * Aquí está la lógica del corazón de la app: marcar un hábito y actualizar puntos.
 */
class RegistroController extends Controller
{
    /**
     * Marca un hábito como cumplido para hoy.
     * Suma los puntos al jugador en la sala y al XP global del usuario.
     * POST /api/registros
     */
    public function marcarHabito(Request $peticion)
    {
        $datos = $peticion->validate([
            'idHabito' => 'required|integer|exists:habitos,idHabito',
        ]);

        $usuario = $peticion->user();
        $habito  = Habito::findOrFail($datos['idHabito']);
        $sala    = $habito->sala;
        $hoy     = Carbon::today();

        // Comprobamos que el usuario está en la sala donde está ese hábito
        $estaEnLaSala = $sala->jugadores()
            ->where('usuarios.idUsuario', $usuario->idUsuario)
            ->exists();

        if (!$estaEnLaSala) {
            return response()->json(['mensaje' => 'No participas en esta sala'], 403);
        }

        // Comprobamos que no haya marcado ya este hábito hoy
        $yaMarcado = RegistroDiario::where('idUsuario', $usuario->idUsuario)
            ->where('idHabito', $habito->idHabito)
            ->where('fecha', $hoy)
            ->exists();

        if ($yaMarcado) {
            return response()->json(['mensaje' => 'Ya marcaste este hábito hoy'], 400);
        }

        // Usamos una transacción: o se hacen TODOS los cambios o ninguno
        // (así no nos quedamos a medias si algo falla)
        DB::transaction(function () use ($usuario, $habito, $sala, $hoy) {
            // 1) Creamos el registro
            RegistroDiario::create([
                'idUsuario'       => $usuario->idUsuario,
                'idSala'          => $sala->idSala,
                'idHabito'        => $habito->idHabito,
                'fecha'           => $hoy,
                'puntosObtenidos' => $habito->puntos,
            ]);

            // 2) Sumamos los puntos al jugador en esta sala
            $sala->jugadores()->updateExistingPivot($usuario->idUsuario, [
                'puntosAcumulados' => DB::raw("puntosAcumulados + {$habito->puntos}"),
            ]);

            // 3) Sumamos XP al usuario
            $usuario->increment('xp', $habito->puntos);
        });

        // Devolvemos el ranking actualizado para que el frontend lo refresque
        $ranking = $sala->jugadores()
            ->orderByPivot('puntosAcumulados', 'desc')
            ->get();

        return response()->json([
            'mensaje'         => '¡Diana!',
            'puntosObtenidos' => $habito->puntos,
            'ranking'         => $ranking,
        ]);
    }

    /**
     * Devuelve los hábitos que el usuario ya ha marcado HOY en una sala.
     * GET /api/salas/{idSala}/registros-hoy
     */
    public function registrosDeHoy($idSala, Request $peticion)
    {
        $usuario = $peticion->user();
        $hoy     = Carbon::today();

        $registros = RegistroDiario::where('idUsuario', $usuario->idUsuario)
            ->where('idSala', $idSala)
            ->where('fecha', $hoy)
            ->pluck('idHabito');

        // Calculamos también los puntos que lleva hoy
        $puntosHoy = RegistroDiario::where('idUsuario', $usuario->idUsuario)
            ->where('idSala', $idSala)
            ->where('fecha', $hoy)
            ->sum('puntosObtenidos');

        return response()->json([
            'habitosMarcados' => $registros,
            'puntosHoy'       => $puntosHoy,
        ]);
    }

    /**
     * Cierra el día: si el usuario marcó al menos 1 hábito hoy, suma 1 a su racha.
     * POST /api/salas/{idSala}/cerrar-dia
     */
    public function cerrarDia($idSala, Request $peticion)
    {
        $usuario = $peticion->user();
        $hoy     = Carbon::today();

        // Comprobamos que haya marcado algo hoy
        $marcoAlgo = RegistroDiario::where('idUsuario', $usuario->idUsuario)
            ->where('idSala', $idSala)
            ->where('fecha', $hoy)
            ->exists();

        if (!$marcoAlgo) {
            return response()->json(['mensaje' => 'Marca al menos un hábito antes'], 400);
        }

        // Sumamos 1 a la racha del jugador en esta sala
        $sala = Sala::findOrFail($idSala);
        $sala->jugadores()->updateExistingPivot($usuario->idUsuario, [
            'rachaActual' => DB::raw('rachaActual + 1'),
        ]);

        $jugadorActualizado = $sala->jugadores()
            ->where('usuarios.idUsuario', $usuario->idUsuario)
            ->first();

        return response()->json([
            'mensaje'      => 'Día cerrado',
            'rachaActual'  => $jugadorActualizado->pivot->rachaActual,
        ]);
    }
}
