<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Controlador de autenticación.
 * Se encarga del registro de nuevos cazadores y del login.
 */
class AuthController extends Controller
{
    /**
     * Registra un usuario nuevo.
     * POST /api/registro
     */
    public function registrar(Request $peticion)
    {
        // 1) Validamos los datos que llegan del frontend
        $datosValidados = $peticion->validate([
            'nombre'     => 'required|string|max:50',
            'apellidos'  => 'required|string|max:100',
            'email'      => 'required|email|unique:usuarios,email',
            'contrasena' => 'required|min:6',
        ]);

        // 2) Creamos el usuario en la base de datos
        $usuario = Usuario::create([
            'nombre'         => $datosValidados['nombre'],
            'apellidos'      => $datosValidados['apellidos'],
            'email'          => $datosValidados['email'],
            // La función Hash::make encripta la contraseña antes de guardarla
            'contrasenaHash' => Hash::make($datosValidados['contrasena']),
            'avatar'         => '🤠',
            'plan'           => 'recluta',
        ]);

        // 3) Generamos un token para que el usuario pueda hacer peticiones autenticadas
        $token = $usuario->createToken('token-cazador')->plainTextToken;

        // 4) Devolvemos los datos del usuario y el token
        return response()->json([
            'mensaje' => '¡Bienvenido, cazador!',
            'usuario' => $usuario,
            'token'   => $token,
        ], 201);
    }

    /**
     * Inicia sesión con email y contraseña.
     * POST /api/login
     */
    public function login(Request $peticion)
    {
        $datos = $peticion->validate([
            'email'      => 'required|email',
            'contrasena' => 'required',
        ]);

        // Buscamos al usuario por email
        $usuario = Usuario::where('email', $datos['email'])->first();

        // Comprobamos que existe Y que la contraseña coincide
        if (!$usuario || !Hash::check($datos['contrasena'], $usuario->contrasenaHash)) {
            return response()->json([
                'mensaje' => 'Email o contraseña incorrectos',
            ], 401);
        }

        // Si todo está bien, creamos un token nuevo
        $token = $usuario->createToken('token-cazador')->plainTextToken;

        return response()->json([
            'mensaje' => 'Sesión iniciada',
            'usuario' => $usuario,
            'token'   => $token,
        ]);
    }

    /**
     * Cierra la sesión actual borrando el token.
     * POST /api/logout
     */
    public function logout(Request $peticion)
    {
        $peticion->user()->currentAccessToken()->delete();

        return response()->json(['mensaje' => 'Sesión cerrada']);
    }

    /**
     * Devuelve los datos del usuario que está logueado.
     * GET /api/yo
     */
    public function yo(Request $peticion)
    {
        return response()->json($peticion->user());
    }
}
