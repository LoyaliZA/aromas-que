<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['QUEJA', 'SUGERENCIA']);
            $table->date('visit_date');
            $table->time('visit_time');
            $table->string('ticket_receipt_path')->nullable()->comment('Ruta de la foto del ticket opcional');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete()->comment('Si la queja es sobre un empleado');
            $table->text('comments');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};