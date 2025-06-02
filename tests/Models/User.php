<?php

declare(strict_types=1);

namespace MrPunyapal\LaravelExtendedRelationships\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use MrPunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

class User extends Model
{
    use HasExtendedRelationships;

    protected $fillable = [
        'id',
        'name',
        'email',
        'companies',
        'user_ids',
        'company_ids',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'companies' => 'array',
        'user_ids' => 'array',
        'company_ids' => 'array',
    ];

    public $timestamps = false;
}
