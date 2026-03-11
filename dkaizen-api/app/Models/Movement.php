<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'movement_type',
        'quantity',
        'movement_date'
    ];

    // Relación: Un movimiento le pertenece a UN Producto
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}