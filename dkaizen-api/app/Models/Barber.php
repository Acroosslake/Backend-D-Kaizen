<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    use HasFactory;

    // Los campos que permitimos llenar masivamente
    protected $fillable = [
        'user_id',
        'rh',
        'eps',
        'contract_type',
        'entry_time',
        'exit_time'
    ];

    // Relación: Un Barbero pertenece a un Usuario (Para traer su nombre y email)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación: Un Barbero tiene muchas Citas
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}