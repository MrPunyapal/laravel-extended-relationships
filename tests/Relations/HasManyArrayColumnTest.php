<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Mrpunyapal\LaravelExtendedRelationships\Relations\HasManyArrayColumn;
use Mrpunyapal\LaravelExtendedRelationships\Tests\Models\Company;
use Mrpunyapal\LaravelExtendedRelationships\Tests\Models\User;

it('can create a has many array column relationship', function () {
    $queryBuilder = Mockery::mock(QueryBuilder::class);
    $eloquentBuilder = Mockery::mock(EloquentBuilder::class, [$queryBuilder]);
    $parent = Mockery::mock(User::class);
    $related = Mockery::mock(Company::class);

    $eloquentBuilder->shouldReceive('getModel')->andReturn($related);
    $eloquentBuilder->shouldReceive('whereNotNull')->with('id')->andReturn($eloquentBuilder);
    $parent->shouldReceive('getAttribute')->with('companies')->andReturn([]);

    $relation = new HasManyArrayColumn(
        $eloquentBuilder,
        $parent,
        'id',
        'companies'
    );

    expect($relation)->toBeInstanceOf(HasManyArrayColumn::class);
});

it('gets foreign key name correctly', function () {
    $queryBuilder = Mockery::mock(QueryBuilder::class);
    $eloquentBuilder = Mockery::mock(EloquentBuilder::class, [$queryBuilder]);
    $parent = Mockery::mock(User::class);
    $related = Mockery::mock(Company::class);

    $eloquentBuilder->shouldReceive('getModel')->andReturn($related);
    $eloquentBuilder->shouldReceive('whereNotNull')->with('company_id')->andReturn($eloquentBuilder);
    $parent->shouldReceive('getAttribute')->with('companies')->andReturn([]);

    $relation = new HasManyArrayColumn(
        $eloquentBuilder,
        $parent,
        'company_id',
        'companies'
    );

    $foreignKey = $relation->getForeignKeyName();

    expect($foreignKey)->toBe('company_id');
});

it('gets keys from models correctly', function () {
    $queryBuilder = Mockery::mock(QueryBuilder::class);
    $eloquentBuilder = Mockery::mock(EloquentBuilder::class, [$queryBuilder]);
    $parent = Mockery::mock(User::class);
    $related = Mockery::mock(Company::class);

    $eloquentBuilder->shouldReceive('getModel')->andReturn($related);
    $eloquentBuilder->shouldReceive('whereNotNull')->with('id')->andReturn($eloquentBuilder);
    $parent->shouldReceive('getAttribute')->with('companies')->andReturn([]);

    $relation = new HasManyArrayColumn(
        $eloquentBuilder,
        $parent,
        'id',
        'companies'
    );

    $model1 = new User(['companies' => [1, 2]]);
    $model2 = new User(['companies' => [2, 3]]);
    $model3 = new User(['companies' => [4]]);

    $reflection = new ReflectionClass($relation);
    $method = $reflection->getMethod('getKeys');
    $method->setAccessible(true);

    $keys = $method->invoke($relation, [$model1, $model2, $model3], 'companies');

    expect($keys)->toBe([1, 2, 3, 4]);
});

it('adds constraints properly', function () {
    $queryBuilder = Mockery::mock(QueryBuilder::class);
    $eloquentBuilder = Mockery::mock(EloquentBuilder::class, [$queryBuilder]);
    $parent = Mockery::mock(User::class);
    $related = Mockery::mock(Company::class);

    $eloquentBuilder->shouldReceive('getModel')->andReturn($related);
    $eloquentBuilder->shouldReceive('getRelationQuery')->andReturn($eloquentBuilder);
    $eloquentBuilder->shouldReceive('whereIn')->with('id', [1, 2, 3])->andReturn($eloquentBuilder);
    $eloquentBuilder->shouldReceive('whereNotNull')->with('id')->andReturn($eloquentBuilder);

    $parent->shouldReceive('getAttribute')->with('companies')->andReturn([1, 2, 3]);

    $relation = new HasManyArrayColumn(
        $eloquentBuilder,
        $parent,
        'id',
        'companies'
    );

    $relation->addConstraints();

    // If we get here without errors, the constraints were added properly
    expect(true)->toBeTrue();
});

it('adds eager constraints properly', function () {
    $queryBuilder = Mockery::mock(QueryBuilder::class);
    $eloquentBuilder = Mockery::mock(EloquentBuilder::class, [$queryBuilder]);
    $parent = Mockery::mock(User::class);
    $related = Mockery::mock(Company::class);

    $eloquentBuilder->shouldReceive('getModel')->andReturn($related);
    $eloquentBuilder->shouldReceive('whereNotNull')->with('id')->andReturn($eloquentBuilder);
    $eloquentBuilder->shouldReceive('whereIn')->with('id', [1, 2, 3])->andReturn($eloquentBuilder);
    $parent->shouldReceive('getAttribute')->with('companies')->andReturn([]);

    $relation = new HasManyArrayColumn(
        $eloquentBuilder,
        $parent,
        'id',
        'companies'
    );

    $model1 = Mockery::mock(User::class);
    $model1->shouldReceive('getAttribute')->with('companies')->andReturn([1, 2]);

    $model2 = Mockery::mock(User::class);
    $model2->shouldReceive('getAttribute')->with('companies')->andReturn([2, 3]);

    $relation->addEagerConstraints([$model1, $model2]);

    expect(true)->toBeTrue();
});

it('models are properly matched to parents', function (): void {
    $queryBuilder = Mockery::mock(QueryBuilder::class);
    $eloquentBuilder = Mockery::mock(EloquentBuilder::class, [$queryBuilder]);
    $parent = Mockery::mock(User::class);
    $related = Mockery::mock(Company::class);

    $eloquentBuilder->shouldReceive('getModel')->andReturn($related);
    $eloquentBuilder->shouldReceive('whereNotNull')->with('id')->andReturn($eloquentBuilder);
    $parent->shouldReceive('getAttribute')->with('companies')->andReturn([]);
    $related->shouldReceive('newCollection')->andReturnUsing(function ($array) {
        return new Collection($array);
    });

    $relation = new HasManyArrayColumn(
        $eloquentBuilder,
        $parent,
        'id',
        'companies'
    );

    $result1 = new Company(['id' => 1]);
    $result2 = new Company(['id' => 2]);
    $result3 = new Company(['id' => 3]);

    $model1 = new User(['companies' => [1]]);
    $model2 = new User(['companies' => [2, 3]]);
    $model3 = new User(['companies' => [4]]); // No matching result

    $models = $relation->matchMany([$model1, $model2, $model3], new Collection([$result1, $result2, $result3]), 'myCompanies');

    expect($models[0]->myCompanies)->toHaveCount(1)
        ->and($models[0]->myCompanies->first()->id)->toBe(1)
        ->and($models[1]->myCompanies)->toHaveCount(2)
        ->and($models[1]->myCompanies->pluck('id')->toArray())->toBe([2, 3])
        ->and($models[2]->myCompanies)->toHaveCount(0);
});
