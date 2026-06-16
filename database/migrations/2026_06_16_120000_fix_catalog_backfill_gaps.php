<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::table('job_positions')->where('name', 'AUXILIAR')->exists()) {
            DB::table('job_positions')->insert([
                'name' => 'AUXILIAR',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::statement('UPDATE employees e JOIN job_positions j ON e.job_position = j.name SET e.job_position_id = j.id WHERE e.job_position IS NOT NULL AND e.job_position_id IS NULL');
        DB::statement('UPDATE users u JOIN roles r ON u.role = r.name SET u.role_id = r.id WHERE u.role IS NOT NULL AND u.role_id IS NULL');
        DB::statement('UPDATE employees e JOIN departments d ON e.department = d.name SET e.department_id = d.id WHERE e.department IS NOT NULL AND e.department_id IS NULL');
        DB::statement('UPDATE pickups p JOIN departments d ON p.department = d.name SET p.department_id = d.id WHERE p.department IS NOT NULL AND p.department_id IS NULL');
        DB::statement('UPDATE customers c JOIN client_types ct ON c.client_type = ct.name SET c.client_type_id = ct.id WHERE c.client_type IS NOT NULL AND c.client_type_id IS NULL');
        DB::statement('UPDATE sales_queue sq JOIN client_types ct ON sq.client_type = ct.name SET sq.client_type_id = ct.id WHERE sq.client_type IS NOT NULL AND sq.client_type_id IS NULL');
        DB::statement('UPDATE sales_queue sq JOIN service_types st ON sq.service_type = st.name SET sq.service_type_id = st.id WHERE sq.service_type IS NOT NULL AND sq.service_type_id IS NULL');
    }

    public function down(): void
    {
        //
    }
};
