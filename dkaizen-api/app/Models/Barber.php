<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    use HasFactory;

protected $fillable = [
    'user_id',
    'specialty',
    'status',
    'email', 
    'phone', 
    'image',
    'rh',
    'eps',
    'contract_type',
    'entry_time',
    'exit_time'
];

protected $casts = [
    'status' => 'boolean',
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}