<?php

namespace App\Models;

use App\Models\Traits\HasCatalogName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosition extends Model
{
    use HasCatalogName, HasFactory;

    protected $fillable = ['name', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function catalogCachePrefix(): string
    {
        return 'catalog_job_position_id';
    }
}
