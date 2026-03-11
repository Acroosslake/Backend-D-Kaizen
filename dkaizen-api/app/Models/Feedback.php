<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'barber_id',
        'comments',
        'rating'
    ];

    // Relación: El feedback lo escribe UN Usuario (Cliente)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación: El feedback califica a UN Barbero
    public function barber()
    {
        return $this->belongsTo(Barber::class);
    }
}