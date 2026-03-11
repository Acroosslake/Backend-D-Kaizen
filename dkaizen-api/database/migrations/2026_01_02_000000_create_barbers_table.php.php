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
        Schema::create('barbers', function (Blueprint $table) {
            $table->id(); // Este es tu cod_barbero
            
            // Llave foránea hacia el usuario (cod_usuario)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Campos específicos del diagrama
            $table->string('rh', 3);
            $table->string('eps', 30);
            $table->enum('tipo_contrato', ['fijo', 'temporal', 'prestacion']); // tipo_contrato
            $table->time('hora_entrada'); // hora_entrada
            $table->time('hora_salida');  // hora_salida
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barbers');
    }
};
