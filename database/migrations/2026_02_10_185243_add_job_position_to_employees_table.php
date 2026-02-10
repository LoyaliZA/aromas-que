<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Agregamos la columna 'job_position' después de 'employee_code'
            // Ponemos 'SELLER' por defecto para que los registros existentes no queden vacíos
            $table->enum('job_position', ['MANAGER', 'CHECKER', 'SELLER', 'ADMIN'])
                  ->default('SELLER') 
                  ->after('employee_code');
        });

        // Opcional: Script para corregir al Admin existente (Si ya existe el ID 1 o código ADM001)
        // Esto asegura que tu usuario Admin actual no se quede con el rol de Vendedor
        DB::table('employees')
            ->where('employee_code', 'ADM001') // O la condición que prefieras
            ->update(['job_position' => 'ADMIN']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('job_position');
        });
    }
};