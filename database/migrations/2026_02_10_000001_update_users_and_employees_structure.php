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
        // 1. Limpieza: Eliminamos la tabla que no sirve
        Schema::dropIfExists('users_and_access');

        // 2. Actualizamos la tabla Users (La identidad)
        Schema::table('users', function (Blueprint $table) {
            // Agregamos el rol según el documento SRS 
            // ADMIN: Acceso total
            // MANAGER: Gerente (Solo PC)
            // CHECKER: Checador/Recepción (Tablet)
            // SELLER: Vendedor (Piso de ventas)
            // CUSTOMER: Cliente (Opcional, si decidimos que se logueen, aunque suelen ser invitados)
            $table->enum('role', ['ADMIN', 'MANAGER', 'CHECKER', 'SELLER', 'CUSTOMER'])
                  ->default('CUSTOMER')
                  ->after('email');
            
            // Un campo para desactivar acceso sin borrar al usuario
            $table->boolean('is_active')->default(true)->after('role');
        });

        // 3. Actualizamos la tabla Employees (La entidad de negocio)
        Schema::table('employees', function (Blueprint $table) {
            // Relación: Un empleado TIENE QUE SER un usuario del sistema
            // Usamos nullable() por si creamos el empleado antes que el usuario, 
            // pero idealmente deberían ir juntos.
            $table->foreignId('user_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('users')
                  ->onDelete('set null'); // Si borras el usuario, el historial del empleado queda, pero sin acceso.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active']);
        });

        // Recrear la tabla vieja si revertimos (aunque estaba vacía)
        Schema::create('users_and_access', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};