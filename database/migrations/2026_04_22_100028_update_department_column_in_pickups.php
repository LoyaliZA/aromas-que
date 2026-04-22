<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cambiamos la columna a VARCHAR(50) para que acepte 'CALLCENTER' y cualquier otro departamento futuro
        DB::statement("ALTER TABLE pickups MODIFY COLUMN department VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        // Si haces rollback, lo regresamos a como estaba originalmente (ajusta si tenías otros valores)
        DB::statement("ALTER TABLE pickups MODIFY COLUMN department ENUM('BELLAROMA', 'AROMAS') NOT NULL");
    }
};