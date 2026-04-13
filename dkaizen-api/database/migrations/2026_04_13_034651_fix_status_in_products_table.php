<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    // 1. Intentamos borrar la restricción por si acaso existe de algún intento fallido
    DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_status_check');

    // 2. Verificamos si la columna existe antes de decidir qué hacer
    if (Schema::hasColumn('products', 'status')) {
        // Si la columna ya existe (quizás como booleano), la cambiamos a string
        Schema::table('products', function (Blueprint $table) {
            $table->string('status')->default('active')->change();
        });
    } else {
        // Si NO existe (que es lo que dice tu error actual), la creamos desde cero
        Schema::table('products', function (Blueprint $table) {
            $table->string('status')->default('active');
        });
    }

    // 3. Limpieza de datos: por si había basura (1, 0, null) la pasamos a 'active'
    DB::table('products')
        ->whereNotIn('status', ['active', 'inactive'])
        ->update(['status' => 'active']);

    // 4. Agregamos el "guardaespaldas" final para que solo acepte estos dos valores
    DB::statement("ALTER TABLE products ADD CONSTRAINT products_status_check CHECK (status IN ('active', 'inactive'))");
}
};
