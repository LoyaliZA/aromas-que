<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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

    // Método para el nuevo rol de Auxiliar
    public function isAuxiliar(): bool
    {
        return $this->role === 'AUXILIAR';
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