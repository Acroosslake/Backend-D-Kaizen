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
        'status', // ✅ Debe ser texto: 'active' o 'inactive'
        'image'
    ];


    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}