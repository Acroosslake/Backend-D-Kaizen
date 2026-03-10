<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'duration',
        'price',
        'status'
    ];
    // Un servicio puede tener MUCHAS citas
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}