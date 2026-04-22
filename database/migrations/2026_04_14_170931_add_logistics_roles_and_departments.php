<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->enum('department', ['AROMAS', 'BELLAROMA', 'CALLCENTER', 'CEDIS', 'NONE'])->default('NONE')->after('job_position');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('ADMIN', 'MANAGER', 'CHECKER', 'SELLER', 'CUSTOMER', 'AUXILIAR', 'BELLAROMA', 'CALLCENTER', 'CEDIS') NOT NULL DEFAULT 'CUSTOMER'");
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('department');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('ADMIN', 'MANAGER', 'CHECKER', 'SELLER', 'CUSTOMER', 'AUXILIAR') NOT NULL DEFAULT 'CUSTOMER'");
    }
};