<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'name',
        'email', // Lo mantenemos aunque sea nullable
        'username', // Tu campo principal
        'password',
        'role', // ADMIN, MANAGER, CHECKER, SALES_POINT
    ];

    /**
     * Atributos ocultos al convertir el modelo a Array/JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casteo automático de tipos.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- HELPER METHODS (Para usar en Blade y Controladores) ---
    // Nos evita escribir if ($user->role === 'ADMIN') cada vez.
    
    public function isAdmin(): bool { return $this->role === 'ADMIN'; }
    public function isManager(): bool { return $this->role === 'MANAGER'; }
    public function isChecker(): bool { return $this->role === 'CHECKER'; }
    public function isSalesPoint(): bool { return $this->role === 'SALES_POINT'; }

    // ... código anterior ...

    /**
     * Accessor: Obtener el Rol en Español legible.
     * Uso en Blade: {{ $user->role_label }}
     */
    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'ADMIN' => 'Administrador',
            'MANAGER' => 'Gerente',
            'CHECKER' => 'Checador (Recepción)',
            'SALES_POINT' => 'Punto de Venta',
            default => 'Usuario',
        };
    }
}