<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cola de espera (TV)
        Schema::create('sales_queue', function (Blueprint $table) {
            $table->id();
            $table->string('client_name', 100);
            $table->enum('source', ['QR_MOBILE', 'MANUAL_KIOSK']);
            
            $table->enum('status', ['WAITING', 'SERVING', 'COMPLETED', 'ABANDONED'])->default('WAITING');
            
            // Relacion manual (nullable) porque el turno puede no existir aun
            $table->unsignedBigInteger('assigned_shift_id')->nullable();
            
            $table->timestamp('queued_at')->useCurrent();
            $table->timestamp('started_serving_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Indice para TV
            $table->index(['status', 'queued_at']);
        });

        // Publicidad
        Schema::create('tv_ads', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->nullable();
            $table->text('media_path');
            $table->enum('media_type', ['IMAGE', 'VIDEO'])->default('IMAGE');
            $table->integer('duration_seconds')->default(15);
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tv_ads');
        Schema::dropIfExists('sales_queue');
    }
};