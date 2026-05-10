<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\LogroController;

/*
|--------------------------------------------------------------------------
| Rutas API de La Caza
|--------------------------------------------------------------------------
| Todas estas rutas tienen el prefijo /api automáticamente.
| Las que tienen el middleware "auth:sanctum" requieren un token válido
| (es decir, el usuario tiene que haber iniciado sesión).
*/

// ─────── Rutas PÚBLICAS (no necesitan estar logueado) ───────

Route::post('/registro', [AuthController::class, 'registrar']);
Route::post('/login',    [AuthController::class, 'login']);

// ─────── Rutas PRIVADAS (requieren login con token) ───────

Route::middleware('auth:sanctum')->group(function () {

    // Datos del usuario logueado y cerrar sesión
    Route::get('/yo',      [AuthController::class, 'yo']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Salas
    Route::get('/salas',           [SalaController::class, 'listarMisSalas']);
    Route::post('/salas',          [SalaController::class, 'crear']);
    Route::get('/salas/{idSala}',  [SalaController::class, 'ver']);
    Route::post('/salas/unirse',   [SalaController::class, 'unirse']);

    // Registro diario de hábitos
    Route::post('/registros',                       [RegistroController::class, 'marcarHabito']);
    Route::get('/salas/{idSala}/registros-hoy',     [RegistroController::class, 'registrosDeHoy']);
    Route::post('/salas/{idSala}/cerrar-dia',       [RegistroController::class, 'cerrarDia']);

    // Logros
    Route::get('/logros', [LogroController::class, 'listar']);

});
