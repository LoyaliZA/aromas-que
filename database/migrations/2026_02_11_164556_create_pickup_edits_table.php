<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickup_edits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickup_id')->constrained('pickups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // Quién hizo el cambio
            $table->json('changes'); // Guardaremos qué cambió: {"pieces": {"old": 2, "new": 5}}
            $table->text('reason')->nullable(); // Razón (Opcional)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_edits');
    }
};