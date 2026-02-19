<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_queue', function (Blueprint $table) {
            // Agregamos el número de turno (Ej: V-001, C-012)
            $table->string('turn_number', 10)->nullable()->after('client_name');
        });
    }

    public function down(): void
    {
        Schema::table('sales_queue', function (Blueprint $table) {
            $table->dropColumn('turn_number');
        });
    }
};