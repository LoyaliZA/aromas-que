<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    /**
     * Campos que permitimos asignar masivamente.
     * Observa que incluimos 'user_id' para poder vincularlo.
     */
    protected $fillable = [
        'user_id',
        'full_name',
        'employee_code',
        'job_position', // <--- AGREGADO
        'is_active',
    ];

    /**
     * Conversión de tipos nativa.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    /**
     * Relación Inversa: Un empleado PERTENECE a una cuenta de Usuario.
     * (Opcional, porque puede haber empleados sin usuario de sistema todavía).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un empleado tiene MÚLTIPLES turnos diarios históricos.
     * Esta relación conecta con la tabla 'daily_shifts'.
     */
    public function dailyShifts(): HasMany
    {
        return $this->hasMany(DailyShift::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accesores y Mutadores (La capa de traducción)
    |--------------------------------------------------------------------------
    */

    /**
     * Ejemplo de lo que preguntaste:
     * Si llamas a $employee->status_label, obtendrás "Activo" o "Inactivo"
     * listo para la vista, sin ensuciar la lógica interna.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->is_active ? 'Activo' : 'Baja Temporal';
    }


    /*
    |--------------------------------------------------------------------------
    | Scopes (Filtros de Búsqueda)
    |--------------------------------------------------------------------------
    */

    /**
     * Filtra solo a los empleados que son vendedores.
     */
    public function scopeActiveSellers($query)
    {
        // Ahora filtramos por la columna REAL del empleado
        return $query->where('is_active', true)
                     ->where('job_position', 'SELLER');
    }
}