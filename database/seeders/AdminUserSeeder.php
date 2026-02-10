<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usamos transaction para asegurar integridad
        DB::transaction(function () {
            
            // 1. Crear el Usuario (Login)
            // Usamos updateOrCreate para que no falle si corres el seeder dos veces
            $user = User::updateOrCreate(
                ['email' => 'realloyal1a@gmail.com'], // Busca por este email
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('12345678'), // Tu contraseña
                    'role' => 'ADMIN',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            // 2. Crear el Perfil de Empleado (Vinculado)
            Employee::updateOrCreate(
                ['employee_code' => 'ADM001'], // Busca por este código
                [
                    'user_id' => $user->id,
                    'full_name' => 'Administrador del Sistema',
                    'job_position' => 'ADMIN', // <--- ¡AQUÍ ESTÁ LA CLAVE!
                    'is_active' => true,
                ]
            );
            
        });
    }
}