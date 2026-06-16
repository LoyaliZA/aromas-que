<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('break_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_lunch')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('break_reasons')->insert([
            ['code' => 'BATHROOM', 'label' => 'Baño', 'sort_order' => 10, 'is_lunch' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'LUNCH', 'label' => 'Comida', 'sort_order' => 20, 'is_lunch' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ERRAND', 'label' => 'Encargo', 'sort_order' => 30, 'is_lunch' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'PACKAGING', 'label' => 'Paquetería', 'sort_order' => 40, 'is_lunch' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'GENERAL', 'label' => 'General', 'sort_order' => 50, 'is_lunch' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::table('daily_shifts', function (Blueprint $table) {
            $table->foreignId('break_reason_id')->nullable()->after('break_reason')->constrained('break_reasons')->nullOnDelete();
        });

        Schema::table('shift_status_logs', function (Blueprint $table) {
            $table->foreignId('break_reason_id')->nullable()->after('reason')->constrained('break_reasons')->nullOnDelete();
        });

        DB::statement('UPDATE daily_shifts d JOIN break_reasons b ON d.break_reason = b.code SET d.break_reason_id = b.id WHERE d.break_reason IS NOT NULL');
        DB::statement('UPDATE shift_status_logs s JOIN break_reasons b ON s.reason = b.code SET s.break_reason_id = b.id WHERE s.reason IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('shift_status_logs', function (Blueprint $table) {
            $table->dropForeign(['break_reason_id']);
            $table->dropColumn('break_reason_id');
        });

        Schema::table('daily_shifts', function (Blueprint $table) {
            $table->dropForeign(['break_reason_id']);
            $table->dropColumn('break_reason_id');
        });

        Schema::dropIfExists('break_reasons');
    }
};
