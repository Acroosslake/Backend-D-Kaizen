<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // ✅ 1. Importamos la interfaz de verificación
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject; // 👈 ¡OJO! Esto es vital para JWT

// ✅ 2. Le decimos a Laravel que este usuario debe verificar su correo
class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * Los atributos que se pueden asignar de forma masiva.
     * Si 'phone' o 'role' no están aquí, Laravel dará error o los ignorará.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',   
        'phone',
        'penalty_fee',  
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- MÉTODOS OBLIGATORIOS PARA JWT ---

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        // Esto añade el rol al token para que el backend lo reconozca rápido
        return [
            'role' => $this->role,
        ];
    }
}