<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Concerns\OnlyForDevelopment;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Cuentas DEMO verificadas: admin, moderador y tres solicitantes.
 * Idempotente: no cambia contraseñas, roles ni datos de cuentas preexistentes.
 */
class InitialUsersSeeder extends Seeder
{
    use OnlyForDevelopment;

    public const DEMO_USERS = [
        ['name' => 'Administrador Demo', 'email' => 'admin@admin.com', 'role' => User::ROLE_ADMIN],
        ['name' => 'Moderador Demo', 'email' => 'moderador@moderador.com', 'role' => User::ROLE_MODERATOR],
        ['name' => 'Usuario Demo Uno', 'email' => 'usuario@usuario.com', 'role' => User::ROLE_USER],
        ['name' => 'Usuario Demo Dos', 'email' => 'usuario2@usuario.com', 'role' => User::ROLE_USER],
        ['name' => 'Usuario Demo Tres', 'email' => 'usuario3@usuario.com', 'role' => User::ROLE_USER],
    ];

    public function run(): void
    {
        $this->assertDevelopmentEnvironment();

        // Por defecto SOLO para pruebas. Se puede cambiar en el .env local.
        $password = (string) env('NESKEROS_DEMO_PASSWORD', 'NeskerOS_Demo#2026');

        if (strlen($password) < 12) {
            throw new RuntimeException('NESKEROS_DEMO_PASSWORD debe tener al menos 12 caracteres.');
        }

        // Comprobar los conflictos ANTES de crear ninguna cuenta.
        foreach (self::DEMO_USERS as $fixture) {
            $existing = User::query()->where('email', $fixture['email'])->first();
            if ($existing && ($existing->role !== $fixture['role']
                    || (bool) $existing->is_admin !== ($fixture['role'] === User::ROLE_ADMIN))) {
                throw new RuntimeException(
                    "La cuenta {$fixture['email']} ya existe con otro rol o is_admin distinto. "
                    . 'No se modifica automáticamente ninguna cuenta existente.'
                );
            }
        }

        foreach (self::DEMO_USERS as $fixture) {
            if (User::query()->where('email', $fixture['email'])->exists()) {
                $this->command?->warn("Ya existe {$fixture['email']}; se conserva su contraseña y configuración.");
                continue;
            }

            // role no está en $fillable por seguridad; aquí se asigna de forma explícita.
            $user = new User([
                'name' => $fixture['name'],
                'email' => $fixture['email'],
                'password' => $password, // El modelo User tiene el cast 'hashed'.
            ]);
            $user->forceFill([
                'role' => $fixture['role'],
                'is_admin' => $fixture['role'] === User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]);
            $user->save();
        }

        $this->command?->info('Cuentas demo verificadas creadas (o conservadas) para los tres roles.');
    }
}
