<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Role;
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
        'role_id',
        'is_active',
        'can_manage_rezagados',
        'can_manage_shifts',
        'can_manage_sellers',
        'receives_prorroga_alerts',
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
            'can_manage_sellers' => 'boolean',
            'receives_prorroga_alerts' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods (Logica de Dominio)
    |--------------------------------------------------------------------------
    | Métodos para verificar roles de manera limpia en controladores y vistas.
    */

    public function catalogRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function resolveRoleName(): ?string
    {
        $this->loadMissing('catalogRole');

        return $this->catalogRole?->name ?? ($this->attributes['role'] ?? null);
    }

    public function hasRole(string ...$roles): bool
    {
        $current = $this->resolveRoleName();

        return $current !== null && in_array($current, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->resolveRoleName() === 'ADMIN';
    }

    public function isManager(): bool
    {
        return $this->resolveRoleName() === 'MANAGER';
    }

    public function isChecker(): bool
    {
        return $this->resolveRoleName() === 'CHECKER';
    }

    public function isSeller(): bool
    {
        return $this->resolveRoleName() === 'SELLER';
    }

    public function isAuxiliar(): bool
    {
        return $this->resolveRoleName() === 'AUXILIAR';
    }

    public function isBellaroma(): bool
    {
        return $this->resolveRoleName() === 'BELLAROMA';
    }

    public function isCallCenter(): bool
    {
        return $this->resolveRoleName() === 'CALLCENTER';
    }

    public function isCedis(): bool
    {
        return $this->resolveRoleName() === 'CEDIS';
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

    public function canManageSellers(): bool
    {
        return $this->can_manage_sellers;
    }

    /**
     * Verifica si recibe alertas de prórroga en el tablero de ventas.
     */
    public function receivesProrrogaAlerts(): bool
    {
        return $this->isAdmin() || (bool) $this->receives_prorroga_alerts;
    }

    public function setRoleAttribute(?string $value): void
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn($this->getTable(), 'role')) {
            $this->attributes['role'] = $value;
        }
        $this->attributes['role_id'] = $value ? Role::idFromName($value) : null;
    }
}