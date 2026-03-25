<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleRating extends Model
{
    use HasFactory;

    protected $table = 'sale_ratings';

    protected $fillable = [
        'sales_queue_id',
        'rater_type',
        'stars',
        'tags',
        'comments',
    ];

    // Casteo automático para manejar el JSON de las etiquetas de forma segura
    protected $casts = [
        'stars' => 'integer',
        'tags' => 'array', 
    ];

    // Relación inversa: Una calificación pertenece a un registro de la cola
    public function salesQueue()
    {
        return $this->belongsTo(SalesQueue::class, 'sales_queue_id');
    }
}