<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $existing = DB::table('client_types')->where('code', 'VIP')->first();

        $payload = [
            'code' => 'VIP',
            'label' => 'VIP',
            'name' => 'VIP',
            'sort_order' => 5,
            'prioritize_in_queue' => true,
            'hide_on_public_tv' => true,
            'use_premium_alert' => true,
            'is_active' => true,
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('client_types')->where('id', $existing->id)->update($payload);

            return;
        }

        DB::table('client_types')->insert(array_merge($payload, [
            'created_at' => $now,
        ]));
    }

    public function down(): void
    {
        $diamanteId = DB::table('client_types')->where('code', 'DIAMANTE')->value('id');
        $vipId = DB::table('client_types')->where('code', 'VIP')->value('id');

        if (!$vipId) {
            return;
        }

        if ($diamanteId) {
            DB::table('customers')->where('client_type_id', $vipId)->update(['client_type_id' => $diamanteId]);
            DB::table('sales_queue')->where('client_type_id', $vipId)->update(['client_type_id' => $diamanteId]);

            if (\Illuminate\Support\Facades\Schema::hasColumn('customers', 'client_type')) {
                DB::table('customers')->where('client_type', 'VIP')->update(['client_type' => 'DIAMANTE']);
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('sales_queue', 'client_type')) {
                DB::table('sales_queue')->where('client_type', 'VIP')->update(['client_type' => 'DIAMANTE']);
            }
        }

        DB::table('client_types')->where('id', $vipId)->delete();
    }
};
