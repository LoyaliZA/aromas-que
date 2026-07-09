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
            $table->boolean('is_senior')->default(false)->after('has_disability');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_queue', function (Blueprint $table) {
            $table->dropColumn('is_senior');
        });
    }
};
