<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $orphanChecks = [
            ['users', 'role', 'role_id'],
            ['employees', 'job_position', 'job_position_id'],
            ['employees', 'department', 'department_id'],
            ['pickups', 'department', 'department_id'],
            ['customers', 'client_type', 'client_type_id'],
            ['sales_queue', 'client_type', 'client_type_id'],
            ['sales_queue', 'service_type', 'service_type_id'],
            ['sales_queue', 'status', 'status_id'],
            ['sales_queue', 'source', 'source_id'],
            ['daily_shifts', 'break_reason', 'break_reason_id'],
        ];

        foreach ($orphanChecks as [$table, $legacy, $fk]) {
            if (!Schema::hasColumn($table, $legacy) || !Schema::hasColumn($table, $fk)) {
                continue;
            }

            DB::statement("UPDATE {$table} t SET {$fk} = NULL WHERE {$legacy} IS NULL");
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });

        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'job_position')) {
                $table->dropColumn('job_position');
            }
            if (Schema::hasColumn('employees', 'department')) {
                $table->dropColumn('department');
            }
        });

        Schema::table('pickups', function (Blueprint $table) {
            if (Schema::hasColumn('pickups', 'department')) {
                $table->dropColumn('department');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'client_type')) {
                $table->dropColumn('client_type');
            }
        });

        Schema::table('sales_queue', function (Blueprint $table) {
            $legacyColumns = ['client_type', 'service_type', 'status', 'source'];
            foreach ($legacyColumns as $column) {
                if (Schema::hasColumn('sales_queue', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('daily_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('daily_shifts', 'break_reason')) {
                $table->dropColumn('break_reason');
            }
        });

        Schema::table('shift_status_logs', function (Blueprint $table) {
            if (Schema::hasColumn('shift_status_logs', 'reason')) {
                $table->dropColumn('reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->after('password');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('job_position')->nullable();
            $table->string('department')->nullable();
        });

        Schema::table('pickups', function (Blueprint $table) {
            $table->string('department')->nullable();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('client_type')->nullable();
        });

        Schema::table('sales_queue', function (Blueprint $table) {
            $table->string('client_type')->nullable();
            $table->string('service_type')->nullable();
            $table->string('status')->nullable();
            $table->string('source')->nullable();
        });

        Schema::table('daily_shifts', function (Blueprint $table) {
            $table->string('break_reason', 50)->nullable();
        });

        Schema::table('shift_status_logs', function (Blueprint $table) {
            $table->string('reason')->nullable();
        });

        DB::statement('UPDATE users u JOIN roles r ON u.role_id = r.id SET u.role = r.name');
        DB::statement('UPDATE employees e JOIN job_positions j ON e.job_position_id = j.id SET e.job_position = j.name');
        DB::statement('UPDATE employees e JOIN departments d ON e.department_id = d.id SET e.department = d.name');
        DB::statement('UPDATE pickups p JOIN departments d ON p.department_id = d.id SET p.department = d.name');
        DB::statement('UPDATE customers c JOIN client_types ct ON c.client_type_id = ct.id SET c.client_type = ct.name');
        DB::statement('UPDATE sales_queue sq JOIN client_types ct ON sq.client_type_id = ct.id SET sq.client_type = ct.name');
        DB::statement('UPDATE sales_queue sq JOIN service_types st ON sq.service_type_id = st.id SET sq.service_type = st.name');
        DB::statement('UPDATE sales_queue sq JOIN queue_statuses qs ON sq.status_id = qs.id SET sq.status = qs.code');
        DB::statement('UPDATE sales_queue sq JOIN queue_sources qso ON sq.source_id = qso.id SET sq.source = qso.code');
        DB::statement('UPDATE daily_shifts d JOIN break_reasons b ON d.break_reason_id = b.id SET d.break_reason = b.code');
        DB::statement('UPDATE shift_status_logs s JOIN break_reasons b ON s.break_reason_id = b.id SET s.reason = b.code');
    }
};
