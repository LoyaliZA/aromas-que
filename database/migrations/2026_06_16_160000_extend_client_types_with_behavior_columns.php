<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('client_types', 'code')) {
            Schema::table('client_types', function (Blueprint $table) {
                $table->string('code')->nullable()->after('id');
                $table->string('label')->nullable()->after('code');
                $table->unsignedSmallInteger('sort_order')->default(100)->after('label');
                $table->unsignedSmallInteger('queue_priority')->nullable()->after('sort_order');
                $table->boolean('prioritize_in_queue')->default(false)->after('queue_priority');
                $table->boolean('hide_on_public_tv')->default(false)->after('prioritize_in_queue');
                $table->boolean('use_premium_alert')->default(false)->after('hide_on_public_tv');
            });
        }

        $now = now();

        $this->upsertClientType('CLIENTES', [
            'label' => 'Clientes',
            'sort_order' => 100,
            'queue_priority' => null,
            'prioritize_in_queue' => false,
            'hide_on_public_tv' => false,
            'use_premium_alert' => false,
        ], ['REGULAR', 'CLIENTES']);

        $this->upsertClientType('DIAMANTE', [
            'label' => 'Diamante',
            'sort_order' => 10,
            'queue_priority' => 10,
            'prioritize_in_queue' => true,
            'hide_on_public_tv' => true,
            'use_premium_alert' => true,
        ], ['VIP', 'DIAMANTE']);

        $disabilityId = DB::table('client_types')
            ->whereIn('name', ['DISCAPACITY'])
            ->orWhere('code', 'DISCAPACITY')
            ->value('id');

        $clientesId = DB::table('client_types')
            ->where('code', 'CLIENTES')
            ->orWhere('name', 'CLIENTES')
            ->value('id');

        if ($disabilityId && $clientesId && $disabilityId !== $clientesId) {
            DB::table('customers')->where('client_type_id', $disabilityId)->update(['client_type_id' => $clientesId]);
            DB::table('sales_queue')->where('client_type_id', $disabilityId)->update(['client_type_id' => $clientesId]);

            if (Schema::hasColumn('customers', 'client_type')) {
                DB::table('customers')->where('client_type', 'DISCAPACITY')->update(['client_type' => 'CLIENTES']);
            }
            if (Schema::hasColumn('sales_queue', 'client_type')) {
                DB::table('sales_queue')->where('client_type', 'DISCAPACITY')->update(['client_type' => 'CLIENTES']);
            }

            DB::table('client_types')->where('id', $disabilityId)->delete();
        }

        foreach ([
            ['code' => 'ORO', 'label' => 'Oro', 'sort_order' => 20, 'queue_priority' => 20],
            ['code' => 'PLATA', 'label' => 'Plata', 'sort_order' => 30, 'queue_priority' => 30],
            ['code' => 'BRONCE', 'label' => 'Bronce', 'sort_order' => 40, 'queue_priority' => 40],
        ] as $tier) {
            $this->upsertClientType($tier['code'], [
                'label' => $tier['label'],
                'sort_order' => $tier['sort_order'],
                'queue_priority' => $tier['queue_priority'],
                'prioritize_in_queue' => false,
                'hide_on_public_tv' => false,
                'use_premium_alert' => false,
            ], [$tier['code']]);
        }

        DB::table('client_types')->whereNull('code')->orWhereNull('label')->update([
            'code' => DB::raw('name'),
            'label' => DB::raw('name'),
            'updated_at' => $now,
        ]);

        if (Schema::hasColumn('client_types', 'code')) {
            Schema::table('client_types', function (Blueprint $table) {
                $table->string('code')->nullable(false)->change();
                $table->string('label')->nullable(false)->change();
            });
        }

        $this->ensureUniqueIndex('client_types', 'client_types_code_unique', 'code');
    }

    public function down(): void
    {
        $now = now();

        DB::table('client_types')->whereIn('code', ['BRONCE', 'PLATA', 'ORO'])->delete();

        if (!DB::table('client_types')->where('code', 'DISCAPACITY')->exists()) {
            DB::table('client_types')->insert([
                'name' => 'DISCAPACITY',
                'code' => 'DISCAPACITY',
                'label' => 'Discapacidad',
                'sort_order' => 100,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('client_types')->where('code', 'CLIENTES')->update([
            'name' => 'REGULAR',
            'code' => 'REGULAR',
            'label' => 'Regular',
            'sort_order' => 100,
            'queue_priority' => null,
            'prioritize_in_queue' => false,
            'hide_on_public_tv' => false,
            'use_premium_alert' => false,
            'updated_at' => $now,
        ]);

        DB::table('client_types')->where('code', 'DIAMANTE')->update([
            'name' => 'VIP',
            'code' => 'VIP',
            'label' => 'VIP',
            'sort_order' => 10,
            'queue_priority' => null,
            'prioritize_in_queue' => false,
            'hide_on_public_tv' => false,
            'use_premium_alert' => false,
            'updated_at' => $now,
        ]);

        if (Schema::hasColumn('client_types', 'code')) {
            Schema::table('client_types', function (Blueprint $table) {
                $table->dropColumn([
                    'code',
                    'label',
                    'sort_order',
                    'queue_priority',
                    'prioritize_in_queue',
                    'hide_on_public_tv',
                    'use_premium_alert',
                ]);
            });
        }
    }

    /**
     * @param  array<int, string>  $legacyNames
     */
    private function upsertClientType(string $code, array $attributes, array $legacyNames = []): void
    {
        $now = now();
        $identifiers = array_values(array_unique(array_merge([$code], $legacyNames)));

        $existing = DB::table('client_types')
            ->where(function ($query) use ($identifiers) {
                $query->whereIn('name', $identifiers)
                    ->orWhereIn('code', $identifiers);
            })
            ->first();

        $payload = array_merge($attributes, [
            'code' => $code,
            'name' => $code,
            'is_active' => true,
            'updated_at' => $now,
        ]);

        if ($existing) {
            DB::table('client_types')->where('id', $existing->id)->update($payload);

            return;
        }

        DB::table('client_types')->insert(array_merge($payload, [
            'created_at' => $now,
        ]));
    }

    private function ensureUniqueIndex(string $table, string $indexName, string $column): void
    {
        $indexes = collect(DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]));

        if ($indexes->isEmpty()) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->unique($column);
            });
        }
    }
};
