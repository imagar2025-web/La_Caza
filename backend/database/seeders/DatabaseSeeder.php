<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Logro;
use App\Models\Usuario;
use App\Models\Sala;
use App\Models\Habito;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * Seeder: rellena la base de datos con datos de prueba.
 * Para ejecutarlo: php artisan db:seed
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─────── 1) Catálogo de LOGROS ───────
        $catalogoLogros = [
            ['codigo' => 'primer_disparo',  'nombre' => 'Primer Disparo',   'descripcion' => 'Marca tu primer hábito',          'icono' => '🎯', 'tipo' => 'puntos', 'objetivo' => 1],
            ['codigo' => 'diana_plena',     'nombre' => 'Diana Plena',      'descripcion' => 'Completa todos los hábitos en un día', 'icono' => '💥', 'tipo' => 'puntos', 'objetivo' => 1],
            ['codigo' => 'racha_3',         'nombre' => 'En Llamas',        'descripcion' => '3 días de racha',                  'icono' => '🔥', 'tipo' => 'racha',  'objetivo' => 3],
            ['codigo' => 'racha_7',         'nombre' => 'Semana Perfecta',  'descripcion' => '7 días consecutivos',              'icono' => '⚡', 'tipo' => 'racha',  'objetivo' => 7],
            ['codigo' => 'racha_30',        'nombre' => 'Mes de Acero',     'descripcion' => '30 días de racha',                 'icono' => '👑', 'tipo' => 'racha',  'objetivo' => 30],
            ['codigo' => 'fundador',        'nombre' => 'Fundador',         'descripcion' => 'Crea tu primera sala',             'icono' => '🏛', 'tipo' => 'fundador', 'objetivo' => 1],
            ['codigo' => 'pts_100',         'nombre' => 'Primer Botín',     'descripcion' => 'Gana 100 puntos totales',          'icono' => '💰', 'tipo' => 'puntos', 'objetivo' => 100],
            ['codigo' => 'pts_500',         'nombre' => 'Gran Cazador',     'descripcion' => 'Gana 500 puntos totales',          'icono' => '💎', 'tipo' => 'puntos', 'objetivo' => 500],
        ];
        foreach ($catalogoLogros as $logro) {
            Logro::create($logro + ['xpRecompensa' => 25]);
        }

        // ─────── 2) USUARIOS de prueba ───────
        $diego = Usuario::create([
            'nombre' => 'Diego', 'apellidos' => 'Delgado',
            'email' => 'diego@lacaza.test',
            'contrasenaHash' => Hash::make('password123'),
            'avatar' => '🦊', 'plan' => 'cazador', 'xp' => 420,
        ]);

        $ivan = Usuario::create([
            'nombre' => 'Iván', 'apellidos' => 'Martín',
            'email' => 'ivan@lacaza.test',
            'contrasenaHash' => Hash::make('password123'),
            'avatar' => '🤠', 'plan' => 'cazador', 'xp' => 530,
        ]);

        $victor = Usuario::create([
            'nombre' => 'Víctor', 'apellidos' => 'Villalta',
            'email' => 'victor@lacaza.test',
            'contrasenaHash' => Hash::make('password123'),
            'avatar' => '👤', 'plan' => 'cazador', 'xp' => 480,
        ]);

        // ─────── 3) Una SALA de ejemplo ───────
        $sala = Sala::create([
            'nombre'           => 'Los Gymbros de Octubre',
            'tematica'         => 'deporte',
            'codigoInvitacion' => Sala::generarCodigoInvitacion(),
            'idAnfitrion'      => $diego->idUsuario,
            'duracionDias'     => 30,
            'fechaInicio'      => Carbon::today(),
            'fechaFin'         => Carbon::today()->addDays(30),
            'maxJugadores'     => 6,
        ]);

        // Añadimos los 3 jugadores a la sala con puntuaciones distintas
        $sala->jugadores()->attach($diego->idUsuario, [
            'fechaUnion' => Carbon::today(), 'puntosAcumulados' => 186, 'rachaActual' => 7,
        ]);
        $sala->jugadores()->attach($ivan->idUsuario, [
            'fechaUnion' => Carbon::today(), 'puntosAcumulados' => 212, 'rachaActual' => 11,
        ]);
        $sala->jugadores()->attach($victor->idUsuario, [
            'fechaUnion' => Carbon::today(), 'puntosAcumulados' => 198, 'rachaActual' => 9,
        ]);

        // ─────── 4) HÁBITOS de la sala (temática deporte) ───────
        $habitos = [
            ['nombre' => 'Entrenar 1 hora',       'icono' => '🏋', 'puntos' => 5,   'descripcion' => 'Fuerza'],
            ['nombre' => 'Caminar 10.000 pasos', 'icono' => '👟', 'puntos' => 3,   'descripcion' => 'Cardio'],
            ['nombre' => 'Beber 3 L de agua',    'icono' => '💧', 'puntos' => 2,   'descripcion' => 'Hidratación'],
            ['nombre' => 'Proteína cada comida', 'icono' => '🥩', 'puntos' => 3,   'descripcion' => 'Nutrición'],
            ['nombre' => 'Dormir 8 horas',        'icono' => '🌙', 'puntos' => 4.5, 'descripcion' => 'Descanso'],
        ];
        foreach ($habitos as $h) {
            Habito::create([
                'idSala'      => $sala->idSala,
                'nombre'      => $h['nombre'],
                'descripcion' => $h['descripcion'],
                'icono'       => $h['icono'],
                'puntos'      => $h['puntos'],
                'activo'      => true,
            ]);
        }

        $this->command->info('✓ Datos de prueba creados.');
        $this->command->info("  Login de prueba: diego@lacaza.test / password123");
        $this->command->info("  Código de la sala demo: {$sala->codigoInvitacion}");
    }
}
