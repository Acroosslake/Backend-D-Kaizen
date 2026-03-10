<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    // 1. Los campos que permitimos llenar masivamente
    protected $fillable = [
        'user_id', 
        'service_id', 
        'appointment_date', 
        'status', 
        'notes'
    ];

    // 2. Relación: Una cita PERTENECE a un Usuario (Cliente)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 3. Relación: Una cita PERTENECE a un Servicio (Corte/Barba)
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}