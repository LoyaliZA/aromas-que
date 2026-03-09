<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_queue', function (Blueprint $table) {
            // Hacemos foreign keys anulables para no romper los registros actuales
            $table->foreignId('customer_id')->nullable()->after('client_name')->constrained('customers')->nullOnDelete();
            $table->foreignId('abandonment_reason_id')->nullable()->after('status')->constrained('abandonment_reasons')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales_queue', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
            $table->dropForeign(['abandonment_reason_id']);
            $table->dropColumn('abandonment_reason_id');
        });
    }
};