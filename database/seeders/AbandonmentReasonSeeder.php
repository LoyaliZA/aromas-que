<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AbandonmentReason;

class AbandonmentReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            'Tiempo de espera muy largo',
            'Solo venía a preguntar / ver',
            'Emergencia personal / Prisa',
            'Otro motivo',
        ];

        foreach ($reasons as $reason) {
            // Usamos firstOrCreate para evitar duplicados si corres el seeder más de una vez
            AbandonmentReason::firstOrCreate([
                'reason' => $reason,
                'is_active' => true
            ]);
        }
    }
}