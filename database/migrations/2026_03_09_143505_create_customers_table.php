<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_number')->unique()->nullable()->comment('ID o número del cliente para sincronización');
            $table->string('name');
            $table->enum('client_type', ['REGULAR', 'VIP', 'DISCAPACITY'])->default('REGULAR');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};