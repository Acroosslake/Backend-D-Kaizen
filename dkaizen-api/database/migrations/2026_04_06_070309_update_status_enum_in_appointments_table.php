<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <--- ¡ESTA LÍNEA ES VITAL!

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Usamos DROP CONSTRAINT IF EXISTS por seguridad
        DB::statement('ALTER TABLE appointments DROP CONSTRAINT IF EXISTS appointments_status_check');
        
        // Agregamos la nueva regla con 'completed'
        DB::statement("ALTER TABLE appointments ADD CONSTRAINT appointments_status_check CHECK (status IN ('pending', 'completed', 'canceled'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        DB::statement('ALTER TABLE appointments DROP CONSTRAINT IF EXISTS appointments_status_check');
        
        // Volvemos a la regla original si decides hacer rollback
        DB::statement("ALTER TABLE appointments ADD CONSTRAINT appointments_status_check CHECK (status IN ('pending', 'canceled'))");
    }
};