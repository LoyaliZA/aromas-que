<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach ([
            ['code' => 'COLABORADORES', 'label' => 'Colaboradores', 'sort_order' => 50],
            ['code' => 'PLATAFORMAS', 'label' => 'Plataformas', 'sort_order' => 60],
        ] as $type) {
            $existing = DB::table('client_types')->where('code', $type['code'])->first();

            if ($existing) {
                DB::table('client_types')->where('id', $existing->id)->update([
                    'label' => $type['label'],
                    'name' => $type['code'],
                    'sort_order' => $type['sort_order'],
                    'is_active' => true,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('client_types')->insert([
                    'code' => $type['code'],
                    'label' => $type['label'],
                    'name' => $type['code'],
                    'sort_order' => $type['sort_order'],
                    'prioritize_in_queue' => false,
                    'hide_on_public_tv' => false,
                    'use_premium_alert' => false,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $clientesId = DB::table('client_types')->where('code', 'CLIENTES')->value('id');

        if ($clientesId) {
            foreach (['COLABORADORES', 'PLATAFORMAS'] as $code) {
                $typeId = DB::table('client_types')->where('code', $code)->value('id');
                if (!$typeId) {
                    continue;
                }

                DB::table('customers')->where('client_type_id', $typeId)->update(['client_type_id' => $clientesId]);
                DB::table('sales_queue')->where('client_type_id', $typeId)->update(['client_type_id' => $clientesId]);
                DB::table('client_types')->where('id', $typeId)->delete();
            }
        }
    }
};
