<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'type',
        'visit_date',
        'visit_time',
        'ticket_receipt_path',
        'employee_id',
        'comments',
    ];

    /**
     * Casting de variables para formatear correctamente las fechas.
     */
    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
        ];
    }

    /**
     * Relación: Una queja puede estar asociada a un empleado en específico.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}