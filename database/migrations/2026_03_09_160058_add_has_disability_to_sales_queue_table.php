<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_queue', function (Blueprint $table) {
            $table->boolean('has_disability')->default(false)->after('client_type');
        });
    }

    public function down(): void
    {
        Schema::table('sales_queue', function (Blueprint $table) {
            $table->dropColumn('has_disability');
        });
    }
};