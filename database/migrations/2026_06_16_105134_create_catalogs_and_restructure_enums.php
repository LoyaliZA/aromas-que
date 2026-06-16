<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create Catalog Tables
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('job_positions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('client_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Insert existing ENUM values
        $roles = ['ADMIN', 'MANAGER', 'CHECKER', 'SELLER', 'CUSTOMER', 'AUXILIAR', 'BELLAROMA', 'CALLCENTER', 'CEDIS'];
        foreach ($roles as $r) { DB::table('roles')->insert(['name' => $r, 'created_at' => now(), 'updated_at' => now()]); }

        $departments = ['AROMAS', 'BELLAROMA', 'CALLCENTER', 'CEDIS', 'NONE'];
        foreach ($departments as $d) { DB::table('departments')->insert(['name' => $d, 'created_at' => now(), 'updated_at' => now()]); }

        $job_positions = ['MANAGER', 'CHECKER', 'SELLER', 'ADMIN'];
        foreach ($job_positions as $jp) { DB::table('job_positions')->insert(['name' => $jp, 'created_at' => now(), 'updated_at' => now()]); }

        $client_types = ['REGULAR', 'VIP', 'DISCAPACITY'];
        foreach ($client_types as $ct) { DB::table('client_types')->insert(['name' => $ct, 'created_at' => now(), 'updated_at' => now()]); }

        $service_types = ['SALES', 'CASHIER'];
        foreach ($service_types as $st) { DB::table('service_types')->insert(['name' => $st, 'created_at' => now(), 'updated_at' => now()]); }

        // 3. Add Foreign Key columns and change old enum columns to VARCHAR nullable
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
        });
        DB::statement("ALTER TABLE users MODIFY role VARCHAR(255) NULL;");

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('job_position_id')->nullable()->constrained('job_positions')->nullOnDelete();
        });
        DB::statement("ALTER TABLE employees MODIFY department VARCHAR(255) NULL;");
        DB::statement("ALTER TABLE employees MODIFY job_position VARCHAR(255) NULL;");

        Schema::table('pickups', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
        });
        DB::statement("ALTER TABLE pickups MODIFY department VARCHAR(255) NULL;");

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('client_type_id')->nullable()->constrained('client_types')->nullOnDelete();
        });
        DB::statement("ALTER TABLE customers MODIFY client_type VARCHAR(255) NULL;");

        Schema::table('sales_queue', function (Blueprint $table) {
            $table->foreignId('client_type_id')->nullable()->constrained('client_types')->nullOnDelete();
            $table->foreignId('service_type_id')->nullable()->constrained('service_types')->nullOnDelete();
        });
        DB::statement("ALTER TABLE sales_queue MODIFY client_type VARCHAR(255) NULL;");
        DB::statement("ALTER TABLE sales_queue MODIFY service_type VARCHAR(255) NULL;");

        // 4. Update data via JOIN to preserve historical info
        DB::statement('UPDATE users u JOIN roles r ON u.role = r.name SET u.role_id = r.id WHERE u.role IS NOT NULL');
        DB::statement('UPDATE employees e JOIN departments d ON e.department = d.name SET e.department_id = d.id WHERE e.department IS NOT NULL');
        DB::statement('UPDATE employees e JOIN job_positions j ON e.job_position = j.name SET e.job_position_id = j.id WHERE e.job_position IS NOT NULL');
        DB::statement('UPDATE pickups p JOIN departments d ON p.department = d.name SET p.department_id = d.id WHERE p.department IS NOT NULL');
        DB::statement('UPDATE customers c JOIN client_types ct ON c.client_type = ct.name SET c.client_type_id = ct.id WHERE c.client_type IS NOT NULL');
        DB::statement('UPDATE sales_queue sq JOIN client_types ct ON sq.client_type = ct.name SET sq.client_type_id = ct.id WHERE sq.client_type IS NOT NULL');
        DB::statement('UPDATE sales_queue sq JOIN service_types st ON sq.service_type = st.name SET sq.service_type_id = st.id WHERE sq.service_type IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_queue', function (Blueprint $table) {
            $table->dropForeign(['service_type_id']);
            $table->dropColumn('service_type_id');
            $table->dropForeign(['client_type_id']);
            $table->dropColumn('client_type_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['client_type_id']);
            $table->dropColumn('client_type_id');
        });

        Schema::table('pickups', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['job_position_id']);
            $table->dropColumn('job_position_id');
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::dropIfExists('service_types');
        Schema::dropIfExists('client_types');
        Schema::dropIfExists('job_positions');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('roles');
    }
};
