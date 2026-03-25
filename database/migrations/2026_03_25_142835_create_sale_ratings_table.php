<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_ratings', function (Blueprint $table) {
            $table->id();

            // Relación directa con el turno específico que se está calificando
            $table->foreignId('sales_queue_id')
                ->constrained('sales_queue')
                ->onDelete('cascade');

            // Identificador para saber si esta fila la llenó el vendedor o el cliente
            $table->enum('rater_type', ['SELLER', 'CLIENT']);

            // Puntuación y retroalimentación
            $table->tinyInteger('stars')->unsigned()->comment('Calificación de 1 a 5 estrellas');
            $table->json('tags')->nullable()->comment('Arreglo de etiquetas predefinidas (Ej. Rápido, Amable)');
            $table->text('comments')->nullable()->comment('Comentarios adicionales de texto libre');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_ratings');
    }
};
