<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_shifts', function (Blueprint $table) {
            // 1800 segundos = 30 minutos por defecto
            $table->integer('lunch_seconds_left')->default(1800)->after('has_taken_lunch');
        });
    }

    public function down(): void
    {
        Schema::table('daily_shifts', function (Blueprint $table) {
            $table->dropColumn('lunch_seconds_left');
        });
    }
};