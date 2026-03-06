<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Actualizamos el ENUM en la tabla users
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('ADMIN', 'MANAGER', 'CHECKER', 'SELLER', 'CUSTOMER', 'AUXILIAR') NOT NULL DEFAULT 'CUSTOMER'");

        // Actualizamos el ENUM en la tabla employees
        DB::statement("ALTER TABLE employees MODIFY COLUMN job_position ENUM('MANAGER', 'CHECKER', 'SELLER', 'ADMIN', 'AUXILIAR') NOT NULL DEFAULT 'SELLER'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a los valores originales (Opcional, pero recomendado por seguridad)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('ADMIN', 'MANAGER', 'CHECKER', 'SELLER', 'CUSTOMER') NOT NULL DEFAULT 'CUSTOMER'");
        DB::statement("ALTER TABLE employees MODIFY COLUMN job_position ENUM('MANAGER', 'CHECKER', 'SELLER', 'ADMIN') NOT NULL DEFAULT 'SELLER'");
    }
};