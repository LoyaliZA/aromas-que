<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('queue_statuses')->insert([
            ['code' => 'WAITING', 'label' => 'En espera', 'sort_order' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'SERVING', 'label' => 'En atención', 'sort_order' => 20, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'COMPLETED', 'label' => 'Completado', 'sort_order' => 30, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ABANDONED', 'label' => 'Abandonado', 'sort_order' => 40, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CANCELED', 'label' => 'Cancelado', 'sort_order' => 50, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('queue_sources', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('queue_sources')->insert([
            ['code' => 'QR_MOBILE', 'label' => 'QR Móvil', 'sort_order' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'MANUAL_KIOSK', 'label' => 'Kiosco Manual', 'sort_order' => 20, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::table('sales_queue', function (Blueprint $table) {
            $table->foreignId('status_id')->nullable()->after('status')->constrained('queue_statuses')->nullOnDelete();
            $table->foreignId('source_id')->nullable()->after('source')->constrained('queue_sources')->nullOnDelete();
        });

        DB::statement('UPDATE sales_queue sq JOIN queue_statuses qs ON sq.status = qs.code SET sq.status_id = qs.id WHERE sq.status IS NOT NULL');
        DB::statement('UPDATE sales_queue sq JOIN queue_sources qso ON sq.source = qso.code SET sq.source_id = qso.id WHERE sq.source IS NOT NULL');

        DB::statement("ALTER TABLE sales_queue MODIFY status VARCHAR(255) NULL");
        DB::statement("ALTER TABLE sales_queue MODIFY source VARCHAR(255) NULL");
    }

    public function down(): void
    {
        Schema::table('sales_queue', function (Blueprint $table) {
            $table->dropForeign(['source_id']);
            $table->dropColumn('source_id');
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
        });

        Schema::dropIfExists('queue_sources');
        Schema::dropIfExists('queue_statuses');
    }
};
