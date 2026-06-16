<?php

namespace App\Models;

use App\Models\Traits\HasCatalogCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakReason extends Model
{
    use HasCatalogCode, HasFactory;

    protected $fillable = ['code', 'label', 'sort_order', 'is_lunch', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_lunch' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function catalogCodeCachePrefix(): string
    {
        return 'catalog_break_reason_id';
    }
}
