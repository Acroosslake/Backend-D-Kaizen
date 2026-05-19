<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // ✅ IMPORTANTE: Sin esto, la migración falla

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    // 1. Eliminamos la restricción vieja por si acaso
    DB::statement('ALTER TABLE services DROP CONSTRAINT IF EXISTS services_status_check');

    // 2. Cambiamos la columna a string primero (para que acepte palabras)
    Schema::table('services', function (Blueprint $table) {
        $table->string('status')->default('active')->change();
    });


    DB::table('services')
        ->whereNotIn('status', ['active', 'inactive'])
        ->update(['status' => 'active']);

    // 4. Ahora sí, como ya no hay "basura", ponemos la restricción
    DB::statement("ALTER TABLE services ADD CONSTRAINT services_status_check CHECK (status IN ('active', 'inactive'))");
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertimos quitando la restricción nueva
        DB::statement('ALTER TABLE services DROP CONSTRAINT IF EXISTS services_status_check');
        
        Schema::table('services', function (Blueprint $table) {
            // Aquí podrías devolverlo a booleano si así estaba antes, 
            // pero para evitar más errores 500, mejor dejarlo como string.
        });
    }
};