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
    Schema::table('products', function (Blueprint $table) {
        // Solo la creamos si no existe, para evitar más errores
        if (!Schema::hasColumn('products', 'category')) {
            $table->string('category')->default('General');
        }
    });
}

public function down()
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn('category');
    });
}
};
