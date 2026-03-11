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
        Schema::create('feedback', function (Blueprint $table) {
            $table->id(); // cod_feedback
            
            // Llaves foráneas a Usuario y Barbero
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('barber_id')->constrained('barbers')->onDelete('cascade');
            
            // Campos del diagrama
            $table->text('comments')->nullable(); // comentarios (lo dejamos nullable por si solo quieren dejar las estrellas)
            $table->enum('rating', ['1', '2', '3', '4', '5']); // calificacion
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
