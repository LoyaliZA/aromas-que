<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_queue', function (Blueprint $table) {
            $table->string('custom_abandonment_reason')->nullable()->after('abandonment_reason_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales_queue', function (Blueprint $table) {
            $table->dropColumn('custom_abandonment_reason');
        });
    }
};