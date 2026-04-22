<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('box_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->string('description')->nullable(); 
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insertamos los valores base que me solicitaste
        DB::table('box_types')->insert([
            ['name' => 'N/A', 'description' => 'Pedido de tienda / No se empacará / Complemento', 'is_active' => true],
            ['name' => '1', 'description' => '1 Caja', 'is_active' => true],
            ['name' => '2', 'description' => '2 Cajas', 'is_active' => true],
            ['name' => '3', 'description' => '3 Cajas', 'is_active' => true],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('box_types');
    }
};