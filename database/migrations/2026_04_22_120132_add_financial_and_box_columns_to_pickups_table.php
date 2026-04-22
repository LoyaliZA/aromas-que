<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickups', function (Blueprint $table) {
            // Agregamos los campos financieros
            $table->decimal('amount', 10, 2)->nullable()->after('department');
            $table->decimal('balance', 10, 2)->nullable()->after('amount');
            
            // Agregamos la llave foránea para las cajas
            $table->foreignId('box_type_id')->nullable()->after('pieces')->constrained('box_types');
        });
    }

    public function down(): void
    {
        Schema::table('pickups', function (Blueprint $table) {
            $table->dropForeign(['box_type_id']);
            $table->dropColumn(['amount', 'balance', 'box_type_id']);
        });
    }
};