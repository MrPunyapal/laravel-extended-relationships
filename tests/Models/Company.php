<?php

declare(strict_types=1);

namespace MrPunyapal\LaravelExtendedRelationships\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use MrPunyapal\LaravelExtendedRelationships\HasExtendedRelationships;

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

    public function employees()
    {
        return $this->belongsToArrayColumn(User::class, 'id', 'company_ids');
    }
}
