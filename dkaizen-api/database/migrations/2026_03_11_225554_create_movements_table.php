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
        Schema::create('movements', function (Blueprint $table) {
            $table->id(); // cod_movimiento
            
            // Llave foránea conectando con el Producto
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            // Campos del diagrama
            $table->enum('movement_type', ['in', 'out']); // tipo_movimiento (entrada o salida)
            $table->integer('quantity'); // cantidad
            $table->date('movement_date'); // fecha_movimiento
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
