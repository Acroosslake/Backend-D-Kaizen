<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

public function up()
{
    // 1. Eliminamos la restricción vieja (la que te da el error)
    DB::statement('ALTER TABLE appointments DROP CONSTRAINT appointments_status_check');

    // 2. Agregamos la nueva restricción con "no-show" incluido
    DB::statement("ALTER TABLE appointments ADD CONSTRAINT appointments_status_check CHECK (status IN ('pending', 'confirmed', 'completed', 'cancelled', 'no-show'))");
}

public function down()
{
    // Por si necesitas volver atrás
    DB::statement('ALTER TABLE appointments DROP CONSTRAINT appointments_status_check');
    DB::statement("ALTER TABLE appointments ADD CONSTRAINT appointments_status_check CHECK (status IN ('pending', 'confirmed', 'completed', 'cancelled'))");
}
};
