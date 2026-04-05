<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject; 
use App\Notifications\CustomResetPassword;

class User extends Authenticatable implements JWTSubject 
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'birth_date', 'pathologies'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    //Métodos requeridos por JWT ---

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        // Guardamos el rol en el token para que React sepa si eres Admin o Cliente
        return [
            'role' => $this->role,
        ];
    }
    
    // Un usuario puede tener MUCHAS citas
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    

    // Un usuario puede dejar MUCHOS feedbacks
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    // Un usuario puede tener MUCHAS sanciones
    public function sanctions()
    {
        return $this->hasMany(Sanction::class);
    }
    public function sendPasswordResetNotification($token)
{
    $this->notify(new CustomResetPassword($token));
}
}