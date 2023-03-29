<?php

namespace Mrpunyapal\LaravelExtendedRelationships\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mrpunyapal\LaravelExtendedRelationships\LaravelExtendedRelationships
 */
class LaravelExtendedRelationships extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Mrpunyapal\LaravelExtendedRelationships\LaravelExtendedRelationships::class;
    }
}
