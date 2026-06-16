<?php

namespace App\Console\Commands;

use App\Models\ClientType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifyCatalogs extends Command
{
    protected $signature = 'catalogs:verify';

    protected $description = 'Report orphaned catalog FK references where legacy string exists but FK is null';

    public function handle(): int
    {
        $checks = [
            ['users', 'role', 'role_id', 'roles', 'name'],
            ['employees', 'job_position', 'job_position_id', 'job_positions', 'name'],
            ['employees', 'department', 'department_id', 'departments', 'name'],
            ['pickups', 'department', 'department_id', 'departments', 'name'],
            ['customers', 'client_type', 'client_type_id', 'client_types', 'code'],
            ['sales_queue', 'client_type', 'client_type_id', 'client_types', 'code'],
            ['sales_queue', 'service_type', 'service_type_id', 'service_types', 'name'],
            ['sales_queue', 'status', 'status_id', 'queue_statuses', 'code'],
            ['sales_queue', 'source', 'source_id', 'queue_sources', 'code'],
            ['daily_shifts', 'break_reason', 'break_reason_id', 'break_reasons', 'code'],
        ];

        $hasOrphans = false;

        foreach ($checks as [$table, $legacyCol, $fkCol, $catalogTable, $catalogColumn]) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $fkCol)) {
                continue;
            }

            $query = DB::table($table)->whereNull($fkCol);
            if (Schema::hasColumn($table, $legacyCol)) {
                $query->whereNotNull($legacyCol);
            } else {
                $this->info("{$table}: legacy column {$legacyCol} dropped, FK-only mode");
                continue;
            }

            $count = $query->count();

            if ($count > 0) {
                $hasOrphans = true;
                $this->error("{$table}: {$count} rows with {$legacyCol} set but {$fkCol} NULL");
            } else {
                $this->info("{$table}: OK");
            }
        }

        if ($this->verifyClientTypes()) {
            $hasOrphans = true;
        }

        if ($hasOrphans) {
            $this->newLine();
            $this->warn('Orphaned catalog references detected. Run migrations or fix_catalog_backfill_gaps.');

            return self::FAILURE;
        }

        $this->info('All catalog FK references are consistent.');

        return self::SUCCESS;
    }

    private function verifyClientTypes(): bool
    {
        if (!Schema::hasTable('client_types') || !Schema::hasColumn('client_types', 'code')) {
            return false;
        }

        $requiredCodes = config('catalog_labels.protected_catalog_names.client_types', []);
        $existingCodes = ClientType::pluck('code')->all();
        $missing = array_diff($requiredCodes, $existingCodes);

        if (!empty($missing)) {
            $this->error('client_types: missing required codes: ' . implode(', ', $missing));
        } else {
            $this->info('client_types: required tier codes present');
        }

        $invalidPriority = ClientType::where('prioritize_in_queue', true)->where('sort_order', '>=', 9999)->count();
        if ($invalidPriority > 0) {
            $this->error("client_types: {$invalidPriority} types prioritize queue with invalid sort_order");
        }

        return !empty($missing) || $invalidPriority > 0;
    }
}
