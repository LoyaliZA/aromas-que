<?php

namespace App\Models;

use App\Models\Traits\HasCatalogCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueStatus extends Model
{
    use HasCatalogCode, HasFactory;

    protected $fillable = ['code', 'label', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function catalogCodeCachePrefix(): string
    {
        return 'catalog_queue_status_id';
    }
}
