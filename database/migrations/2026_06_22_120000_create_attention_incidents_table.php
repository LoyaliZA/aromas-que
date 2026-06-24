<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attention_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_shift_id')->nullable()->constrained('daily_shifts')->cascadeOnDelete();
            $table->foreignId('sales_queue_id')->nullable()->constrained('sales_queue')->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('turn_number')->nullable();
            $table->string('client_name')->nullable();
            $table->string('reason')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attention_incidents');
    }
};
