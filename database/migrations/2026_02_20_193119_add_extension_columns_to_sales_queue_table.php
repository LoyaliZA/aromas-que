<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_queue', function (Blueprint $table) {
            // Para reiniciar el contador de 15 minutos
            $table->timestamp('last_extended_at')->nullable()->after('started_serving_at');
            // Para saber cuántas veces el vendedor solicitó más tiempo (KPI)
            $table->integer('extension_count')->default(0)->after('last_extended_at');
        });
    }

    public function down(): void
    {
        Schema::table('sales_queue', function (Blueprint $table) {
            $table->dropColumn(['last_extended_at', 'extension_count']);
        });
    }
};