<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabla sales_queue: Agregar tipo de servicio (Venta vs Caja)
        Schema::table('sales_queue', function (Blueprint $table) {
            $table->enum('service_type', ['SALES', 'CASHIER'])
                  ->default('SALES')
                  ->after('client_name'); // Lo ponemos visualmente después del nombre
        });

        // 2. Tabla pickups: Agregar ruta de evidencia (Foto al entregar)
        Schema::table('pickups', function (Blueprint $table) {
            $table->text('evidence_path')
                  ->nullable()
                  ->after('signature_path');
        });

        // 3. Tabla daily_shifts: Agregar motivo de break (Baño, Comida, etc.)
        Schema::table('daily_shifts', function (Blueprint $table) {
            $table->string('break_reason', 50)
                  ->nullable()
                  ->after('current_status');
        });

        // 4. Tabla users: Permiso especial para ver paquetes viejos (>15 días)
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('can_manage_rezagados')
                  ->default(false)
                  ->after('role');
        });
    }

    /**
     * Reverse the migrations.
     * (Esto se ejecuta si corremos "migrate:rollback", deshaciendo los cambios)
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('can_manage_rezagados');
        });

        Schema::table('daily_shifts', function (Blueprint $table) {
            $table->dropColumn('break_reason');
        });

        Schema::table('pickups', function (Blueprint $table) {
            $table->dropColumn('evidence_path');
        });

        Schema::table('sales_queue', function (Blueprint $table) {
            $table->dropColumn('service_type');
        });
    }
};