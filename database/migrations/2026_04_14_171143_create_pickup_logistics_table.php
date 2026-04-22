<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickup_logistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickup_id')->constrained('pickups')->cascadeOnDelete();
            $table->foreignId('bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained('couriers')->nullOnDelete();
            
            $table->decimal('note_amount', 10, 2)->nullable();
            $table->decimal('credit_balance', 10, 2)->default(0);
            $table->string('tracking_number', 100)->nullable();
            $table->string('box_number', 50)->nullable();
            $table->boolean('is_local_delivery')->default(false);
            $table->boolean('is_store_pickup')->default(false);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_logistics');
    }
};