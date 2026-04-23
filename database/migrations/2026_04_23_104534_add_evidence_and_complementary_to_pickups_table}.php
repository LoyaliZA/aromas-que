<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickups', function (Blueprint $table) {
            // Evidencia tomada por el Gerente al registrar las piezas
            $table->string('initial_evidence_path')->nullable()->after('evidence_path');
            // Evidencia tomada por el Checador al recibir el paquete
            $table->string('package_evidence_path')->nullable()->after('initial_evidence_path');
            
            // Lógica de resguardos complementarios
            $table->boolean('is_complementary')->default(false)->after('department');
            // Llave foránea recursiva para vincular al resguardo original
            $table->foreignId('parent_pickup_id')->nullable()->after('is_complementary')->constrained('pickups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pickups', function (Blueprint $table) {
            $table->dropForeign(['parent_pickup_id']);
            $table->dropColumn([
                'initial_evidence_path', 
                'package_evidence_path', 
                'is_complementary', 
                'parent_pickup_id'
            ]);
        });
    }
};