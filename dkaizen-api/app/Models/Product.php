<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'purchase_price',
        'stock'
    ];

    //producto puede tener MUCHOS movimientos (entradas y salidas)
    public function movements()
    {
        return $this->hasMany(Movement::class);
    }
    }