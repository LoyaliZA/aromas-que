<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar columna de notas de corrección a la tabla pickups
        Schema::table('pickups', function (Blueprint $table) {
            if (!Schema::hasColumn('pickups', 'correction_notes')) {
                $table->text('correction_notes')->nullable()->after('notes');
            }
        });

        // 2. Insertar los nuevos estatus en el catálogo (Sin is_active)
        $statuses = [
            [
                'code' => 'PRE_REGISTERED',
                'name' => 'Pre-Registro',
                'color' => 'sky',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'PENDING_CONFIRMATION',
                'name' => 'Pendiente Confirmación',
                'color' => 'amber',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'NEEDS_CORRECTION',
                'name' => 'Requiere Corrección',
                'color' => 'rose',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($statuses as $status) {
            DB::table('pickup_statuses')->updateOrInsert(
                ['code' => $status['code']],
                $status
            );
        }
    }

    public function down(): void
    {
        Schema::table('pickups', function (Blueprint $table) {
            $table->dropColumn('correction_notes');
        });

        DB::table('pickup_statuses')
            ->whereIn('code', ['PRE_REGISTERED', 'PENDING_CONFIRMATION', 'NEEDS_CORRECTION'])
            ->delete();
    }
};