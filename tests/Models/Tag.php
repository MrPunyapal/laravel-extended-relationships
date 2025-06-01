<?php

declare(strict_types=1);

namespace Mrpunyapal\LaravelExtendedRelationships\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Mrpunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

class Tag extends Model
{
    use HasExtendedRelationships;

    protected $fillable = [
        'id',
        'name',
        'post_ids',
    ];

    protected $casts = [
        'post_ids' => 'array',
    ];

    public $timestamps = false;
}
