<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Mrpunyapal\LaravelExtendedRelationships\Relations\HasManyArrayColumn;

it('models are properly matched to parents', function (): void {
    $relation = getRelation();

    $result1 = new HasManyArrayColumnModelStub();
    $result1->foreign_key = 1;
    $result2 = new HasManyArrayColumnModelStub();
    $result2->foreign_key = 2;
    $result3 = new HasManyArrayColumnModelStub();
    $result3->foreign_key = 2;

    $model1 = new HasManyArrayColumnModelStub();
    $model1->companies = [1];
    $model2 = new HasManyArrayColumnModelStub();
    $model2->companies = [2];
    $model3 = new HasManyArrayColumnModelStub();
    $model3->companies = [3];

    $relation->getRelated()->shouldReceive('newCollection')->andReturnUsing(function ($array) {
        return new Collection($array);
    });

    $models = $relation->match([$model1, $model2, $model3], new Collection([$result1, $result2, $result3]), 'foo');

    expect($models[0]->foo)->toHaveCount(1)
        ->get(0)->foreign_key->toBe(1)
        ->and($models[1]->foo)->toHaveCount(2)
        ->get(0)->foreign_key->toBe(2)
        ->get(1)->foreign_key->toBe(2)
        ->and($models[2]->foo)->toHaveCount(0);
});

function getRelation()
{
    $queryBuilder = Mockery::mock(QueryBuilder::class);
    $eloquentBuilder = Mockery::mock(EloquentBuilder::class, [$queryBuilder]);

    $parent = Mockery::mock(Model::class);
    $related = Mockery::mock(Model::class);

    $eloquentBuilder->shouldReceive('getModel')->andReturn($related);
    $eloquentBuilder->shouldReceive('whereIn')->with('foreign_key', [1, 2]);
    $eloquentBuilder->shouldReceive('whereNotNull')->with('foreign_key');

    $parent->shouldReceive('getAttribute')->with('companies')->andReturn([1, 2]);

    return new HasManyArrayColumn($eloquentBuilder, $parent, 'foreign_key', 'companies');
}

class HasManyArrayColumnModelStub extends Model
{
    public int $foreign_key;
}
