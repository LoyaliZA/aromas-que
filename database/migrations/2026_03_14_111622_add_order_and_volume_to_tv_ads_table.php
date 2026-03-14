<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tv_ads', function (Blueprint $table) {
            $table->integer('order_index')->default(0)->after('is_active');
            $table->integer('volume')->default(100)->after('order_index'); // 0 a 100
        });
    }
    public function down(): void {
        Schema::table('tv_ads', function (Blueprint $table) {
            $table->dropColumn(['order_index', 'volume']);
        });
    }
};