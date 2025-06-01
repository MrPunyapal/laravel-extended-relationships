<?php

declare(strict_types=1);

namespace Mrpunyapal\LaravelExtendedRelationships\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Mrpunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

class Post extends Model
{
    use HasExtendedRelationships;

    protected $fillable = [
        'id',
        'title',
        'content',
        'user_id',
        'tag_ids',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'tag_ids' => 'array',
    ];

    public $timestamps = false;
}
