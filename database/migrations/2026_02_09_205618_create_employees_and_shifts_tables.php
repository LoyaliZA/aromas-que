<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Empleados
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 100);
            $table->string('employee_code', 20)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Turnos Diarios (Hub de Vendedores)
        Schema::create('daily_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('work_date');
            
            // Estados: ONLINE, BREAK, OFFLINE, BUSY
            $table->string('current_status', 20)->default('OFFLINE');
            
            // Optimizacion: Bandera para inactividad > 15min
            $table->boolean('flagged_as_idle')->default(false);
            
            $table->integer('customers_served_count')->default(0);
            $table->timestamp('last_status_change_at')->useCurrent();
            $table->timestamp('last_action_at')->useCurrent(); // Heartbeat

            $table->timestamps();

            // Indice compuesto para velocidad en Polling
            $table->index(['work_date', 'current_status', 'flagged_as_idle']);
        });

        // 3. Log de Cambios de Estado (Auditoria)
        Schema::create('shift_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_shift_id')->constrained('daily_shifts')->onDelete('cascade');
            $table->string('previous_status', 20)->nullable();
            $table->string('new_status', 20);
            $table->timestamp('changed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_status_logs');
        Schema::dropIfExists('daily_shifts');
        Schema::dropIfExists('employees');
    }
};