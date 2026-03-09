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
        Schema::table('sales_queue', function (Blueprint $table) {
            $table->enum('client_type', ['REGULAR', 'VIP', 'DISCAPACITY'])
                  ->default('REGULAR')
                  ->after('client_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_queue', function (Blueprint $table) {
            $table->dropColumn('client_type');
        });
    }
};