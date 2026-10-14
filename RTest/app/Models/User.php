<?php

namespace App\Models;

<<<<<<< HEAD
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
=======
use Illuminate\Contracts\Auth\MustVerifyEmail;
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

<<<<<<< HEAD

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

=======
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

    /**
     * Atributos asignables de forma masiva.
     *
     * @var array<int, string>
     */
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'google_token',
        'google_refresh_token',
    ];

<<<<<<< HEAD
=======
    /**
     * Atributos ocultos para la serialización del modelo.
     *
     * @var array<int, string>
     */
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
    protected $hidden = [
        'password',
        'remember_token',
        'google_token',
        'google_refresh_token',
    ];

<<<<<<< HEAD

    /**
     * Get the attributes that should be cast.
=======
    /**
     * Define las conversiones de tipo para los atributos especificados.
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
<<<<<<< HEAD
            'password' => 'hashed',
        ];
    }

    public function user()
    {
        return $this->belongsTo(Profiles::class);
    }
=======
            'password'          => 'hashed',
        ];
    }
>>>>>>> d68ec3eddcc45d2f309e1556c404069e10607ab2
}
