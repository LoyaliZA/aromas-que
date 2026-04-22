<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens; // <-- Importar el trait de Sanctum

class User extends Authenticatable
{
    // <-- Agregar HasApiTokens al inicio
    use HasApiTokens, HasFactory, Notifiable; 

    /**
     * Los atributos que se pueden asignar masivamente (Mass Assignable).
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'can_manage_rezagados',
        'can_manage_shifts',
    ];

    /**
     * Atributos que deben ocultarse en las respuestas JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Los "Casts" para conversión de tipos de datos.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'can_manage_rezagados' => 'boolean',
            'can_manage_shifts' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods (Logica de Dominio)
    |--------------------------------------------------------------------------
    | Métodos para verificar roles de manera limpia en controladores y vistas.
    */

    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function isManager(): bool
    {
        return $this->role === 'MANAGER';
    }

    public function isChecker(): bool
    {
        return $this->role === 'CHECKER';
    }

    public function isSeller(): bool
    {
        return $this->role === 'SELLER';
    }

    public function isAuxiliar(): bool
    {
        return $this->role === 'AUXILIAR';
    }

    public function isBellaroma(): bool
    {
        return $this->role === 'BELLAROMA';
    }

    public function isCallCenter(): bool
    {
        return $this->role === 'CALLCENTER';
    }

    public function isCedis(): bool
    {
        return $this->role === 'CEDIS';
    }

    /**
     * Verifica si el usuario tiene la cuenta activa.
     */
    public function canAccess(): bool
    {
        return $this->is_active;
    }

    /**
     * Verifica si tiene permiso para gestionar rezagados.
     */
    public function canManageRezagados(): bool
    {
        return $this->can_manage_rezagados;
    }

    /**
     * Verifica si tiene permiso para gestionar los turnos.
     */
    public function canManageShifts(): bool
    {
        return $this->can_manage_shifts;
    }
}