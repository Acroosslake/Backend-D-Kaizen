<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    //Los campos que permitimos llenar masivamente
    protected $fillable = [
        'user_id', 
        'service_id', 
        'appointment_date', 
        'status', 
        'notes'
    ];
    // Relación: Una cita es atendida por UN Barbero
    public function barber()
    {
        return $this->belongsTo(Barber::class);
    }

    //Relación: Una cita PERTENECE a un Usuario (Cliente)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //Relación: Una cita PERTENECE a un Servicio (Corte/Barba)
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    // Una cita puede generar UNA sanción (por no ir o cancelar tarde)
    public function sanction()
    {
        return $this->hasOne(Sanction::class);
    }
}