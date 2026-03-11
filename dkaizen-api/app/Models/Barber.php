<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{

// Un barbero puede recibir MUCHOS feedbacks
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }
}
