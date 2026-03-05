<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickups', function (Blueprint $table) {
            // Guardará la fecha y hora exacta en la que el checador acepta el paquete
            $table->timestamp('received_by_checker_at')->nullable()->after('evidence_path');
        });
    }

    public function down(): void
    {
        Schema::table('pickups', function (Blueprint $table) {
            $table->dropColumn('received_by_checker_at');
        });
    }
};