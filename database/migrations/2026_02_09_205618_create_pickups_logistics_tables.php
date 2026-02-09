<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // TABLA CALIENTE (Operacion del dia)
        Schema::create('pickups', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_folio', 50)->unique();
            $table->date('ticket_date'); // Requerido por SRS
            
            $table->string('client_ref_id', 50)->nullable();
            $table->string('client_name', 150);
            
            $table->enum('department', ['AROMAS', 'BELLAROMA']);
            $table->integer('pieces');
            
            $table->enum('status', ['IN_CUSTODY', 'DELIVERED'])->default('IN_CUSTODY');
            
            // Datos de entrega
            $table->string('receiver_name', 150)->nullable();
            $table->boolean('is_third_party')->default(false);
            $table->text('signature_path')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            $table->timestamps();

            $table->index(['ticket_folio', 'client_name']);
        });

        // TABLA FRIA (Archivo Historico)
        Schema::create('pickups_archive', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_pickup_id')->nullable();
            $table->string('ticket_folio', 50);
            $table->string('client_ref_id', 50)->nullable();
            $table->string('client_name', 150);
            $table->string('department', 20);
            $table->integer('pieces')->nullable();
            
            $table->string('receiver_name', 150)->nullable();
            $table->boolean('is_third_party')->default(false);
            $table->text('signature_path')->nullable();
            
            $table->timestamp('original_created_at')->nullable();
            $table->timestamp('archived_at')->useCurrent();

            $table->index('ticket_folio');
            $table->index('archived_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickups_archive');
        Schema::dropIfExists('pickups');
    }
};