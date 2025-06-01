<?php

declare(strict_types=1);

namespace Mrpunyapal\LaravelExtendedRelationships\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Mrpunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

class Company extends Model
{
    use HasExtendedRelationships;

    protected $fillable = [
        'id',
        'name',
        'user_ids',
        'founder_ids',
    ];

    protected $casts = [
        'user_ids' => 'array',
        'founder_ids' => 'array',
    ];

    public $timestamps = false;
}
