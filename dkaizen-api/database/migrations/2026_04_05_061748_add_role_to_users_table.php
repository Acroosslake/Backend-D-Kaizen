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
    Schema::table('users', function (Blueprint $table) {
        // Por defecto todos son 'cliente'
        $table->string('role')->default('client'); 
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
    public function handle($request, Closure $next, $role)
{
    if (auth()->user()->role !== $role) {
        return response()->json(['message' => 'No tienes permiso, fiera.'], 403);
    }
    return $next($request);
}
};
