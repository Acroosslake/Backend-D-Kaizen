<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barbers', function (Blueprint $table) {
            // "Si NO existe la columna specialty, créala"
            if (!Schema::hasColumn('barbers', 'specialty')) {
                $table->string('specialty')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('barbers', 'rh')) {
                $table->string('rh', 5)->nullable();
            }
            if (!Schema::hasColumn('barbers', 'eps')) {
                $table->string('eps')->nullable();
            }
            if (!Schema::hasColumn('barbers', 'contract_type')) {
                $table->string('contract_type')->nullable();
            }
            if (!Schema::hasColumn('barbers', 'entry_time')) {
                $table->string('entry_time')->nullable();
            }
            if (!Schema::hasColumn('barbers', 'exit_time')) {
                $table->string('exit_time')->nullable();
            }
        });
    }

    public function down(): void
    {

    }
};