<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // cod_producto
            
            // Campos del diagrama
            $table->string('name'); // nombre
            $table->decimal('price', 10, 2); // precio (al que lo vendes en la barbería)
            $table->decimal('purchase_price', 10, 2); // valor_compra (a cómo te lo dejó el proveedor)
            $table->integer('stock')->default(0); // cantidad (inicia en 0, los Movimientos lo irán sumando o restando)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
