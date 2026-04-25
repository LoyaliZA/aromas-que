<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pickup_deleted_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_pickup_id')->nullable();
            $table->string('ticket_folio');
            $table->foreignId('deleted_by')->constrained('users');
            $table->json('pickup_data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_deleted_audits');
    }
};
