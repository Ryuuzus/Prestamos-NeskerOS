<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * ==================================================================================================
 * DESCRIPCIÓN GENERAL:
 * Modelo de Usuario (User).
 * 
 * Representa la entidad de usuarios autenticables dentro del sistema. Soporta autenticación nativa,
 * verificación de correo electrónico y login social mediante Google (Socialite).
 * ==================================================================================================
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    // roles válidos: nuevos registros usan 'user' por defecto en la migración.
    public const ROLE_USER = 'user';
    public const ROLE_MODERATOR = 'moderator';
    public const ROLE_ADMIN = 'admin';

    public const ROLES = [self::ROLE_USER, self::ROLE_MODERATOR, self::ROLE_ADMIN];

    /**
     * La columna role NO está en $fillable para evitar que registro, OAuth o
     * edición de perfil puedan elevar privilegios por mass assignment.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }


    /**
     * Atributos asignables de forma masiva.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'google_token',
        'google_refresh_token',
    ];

    /**
     * Atributos ocultos para la serialización del modelo.
     *
     * @var array<int, string>
     */

    protected $hidden = [
        'password',
        'remember_token',
        'google_token',
        'google_refresh_token',
    ];

    /**
     * Define las conversiones de tipo para los atributos especificados.

     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
        ];
    }

}
