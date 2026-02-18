<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // CORRECCIÓN: Usamos 'job_position' que es el nombre real en tu BD
            $table->boolean('appears_in_sales_queue')
                  ->default(false)
                  ->after('job_position'); 
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('appears_in_sales_queue');
        });
    }
};