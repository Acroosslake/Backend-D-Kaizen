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
        'status',
        'image'
    ]; // ✅ Asegúrate de que termine así

    protected $casts = [
        'status' => 'boolean',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}