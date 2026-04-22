<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Crear la tabla catálogo
        Schema::create('pickup_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ej: CAPTURED
            $table->string('name'); // Ej: Capturado
            $table->string('color')->default('gray'); // Para pintar los badges en la UI
            $table->integer('sort_order')->default(0); // Para ordenar la línea de tiempo
            $table->timestamps();
        });

        // 2. Insertar los estatus base que usaremos en T.E.R.A.
        DB::table('pickup_statuses')->insert([
            ['code' => 'CAPTURED', 'name' => 'Capturado', 'color' => 'blue', 'sort_order' => 10],
            ['code' => 'IN_PROCESS_CEDIS', 'name' => 'En Proceso CEDIS', 'color' => 'yellow', 'sort_order' => 20],
            ['code' => 'PACKED', 'name' => 'Empacado', 'color' => 'orange', 'sort_order' => 30],
            ['code' => 'DISPATCHED', 'name' => 'Enviado / En Ruta', 'color' => 'purple', 'sort_order' => 40],
            ['code' => 'IN_STORE', 'name' => 'Recibido en Tienda', 'color' => 'indigo', 'sort_order' => 50],
            ['code' => 'IN_CUSTODY', 'name' => 'En Resguardo', 'color' => 'emerald', 'sort_order' => 60],
            ['code' => 'DELIVERED', 'name' => 'Entregado al Cliente', 'color' => 'green', 'sort_order' => 70],
            ['code' => 'CANCELLED', 'name' => 'Cancelado', 'color' => 'red', 'sort_order' => 99],
        ]);

        // 3. Modificar la tabla pickups
        Schema::table('pickups', function (Blueprint $table) {
            // Agregamos la nueva columna foránea
            $table->foreignId('status_id')->nullable()->after('pieces')->constrained('pickup_statuses');
        });

        // 4. Migrar los datos existentes (Mapea el ENUM viejo al ID nuevo)
        DB::statement("UPDATE pickups p JOIN pickup_statuses ps ON p.status = ps.code SET p.status_id = ps.id");

        // 5. Borrar la columna vieja y hacer la nueva obligatoria
        Schema::table('pickups', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        DB::statement("ALTER TABLE pickups MODIFY COLUMN status_id bigint unsigned NOT NULL");
    }

    public function down(): void
    {
        Schema::table('pickups', function (Blueprint $table) {
            $table->string('status')->default('IN_CUSTODY')->after('status_id');
        });

        DB::statement("UPDATE pickups p JOIN pickup_statuses ps ON p.status_id = ps.id SET p.status = ps.code");

        Schema::table('pickups', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
        });

        Schema::dropIfExists('pickup_statuses');
    }
};