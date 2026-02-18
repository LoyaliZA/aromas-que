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
     * Senior Tip: Siempre controla estrictamente qué entra aquí para evitar
     * que alguien se asigne el rol de ADMIN enviando un campo extra en un formulario.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',      // Agregado: Vital para definir permisos
        'is_active', // Agregado: Para el "Soft Ban" (quitar acceso sin borrar)
        'can_manage_rezagados', // Nuevo: Permiso especial para logística vieja
    ];

    /**
     * Atributos que deben ocultarse en las respuestas JSON (APIs).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Los "Casts" convierten datos crudos de SQL a tipos nativos de PHP.
     * Esto evita que tengas que estar comparando con 1 o 0 manualmente.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean', // SQL guarda 1/0, PHP ve true/false
            'can_manage_rezagados' => 'boolean', // Igual aquí
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones (Relationships)
    |--------------------------------------------------------------------------
    | Definimos cómo se conecta este Usuario con el resto del sistema.
    */

    /**
     * Relación Uno a Uno: Un Usuario PUEDE tener un perfil de Empleado asociado.
     * No todos los usuarios son empleados (ej. un Cliente), por eso es nullable implícitamente.
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods (Logica de Dominio)
    |--------------------------------------------------------------------------
    | Estos métodos encapsulan lógica. En lugar de repetir strings por todo
    | el proyecto, centralizamos las preguntas aquí.
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

    /**
     * Verifica si el usuario puede acceder al sistema.
     * Útil para bloquear el login incluso si la contraseña es correcta.
     */
    public function canAccess(): bool
    {
        return $this->is_active;
    }

    /**
     * Nuevo Helper: Verifica si tiene permiso para la "White List" de rezagados.
     */
    public function canManageRezagados(): bool
    {
        // Solo un Manager o Admin debería poder tener este flag true,
        // pero por seguridad, validamos el flag directamente.
        return $this->can_manage_rezagados;
    }
}