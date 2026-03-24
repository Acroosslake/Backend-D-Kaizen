<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up()
    {
        Schema::create('barbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // ESTAS SON LAS COLUMNAS QUE POSTGRES NO ENCUENTRA
            $table->string('rh', 5)->nullable();
            $table->string('eps', 50)->nullable();
            $table->enum('contract_type', ['fijo', 'temporal', 'prestacion'])->default('prestacion');
            $table->time('entry_time')->nullable();
            $table->time('exit_time')->nullable();

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
