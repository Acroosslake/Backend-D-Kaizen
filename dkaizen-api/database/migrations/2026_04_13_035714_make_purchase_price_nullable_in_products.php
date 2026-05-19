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
        // Hacemos que el precio de compra sea opcional (nullable)
        $table->decimal('purchase_price', 10, 2)->nullable()->change();
    });
}

public function down()
{
    Schema::table('products', function (Blueprint $table) {
        $table->decimal('purchase_price', 10, 2)->nullable(false)->change();
    });
}
};
