<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Mrpunyapal\LaravelExtendedRelationships\Relations\BelongsToArrayColumn;
use Mrpunyapal\LaravelExtendedRelationships\Tests\Models\Company;
use Mrpunyapal\LaravelExtendedRelationships\Tests\Models\User;

it('can create a belongs to array column relationship', function () {
    $queryBuilder = Mockery::mock(QueryBuilder::class);
    $eloquentBuilder = Mockery::mock(EloquentBuilder::class, [$queryBuilder]);
    $child = Mockery::mock(Company::class);
    $related = Mockery::mock(User::class);

    $eloquentBuilder->shouldReceive('getModel')->andReturn($related);
    $eloquentBuilder->shouldReceive('getQuery')->andReturn($queryBuilder);
    $queryBuilder->shouldReceive('when')->once()->andReturn($queryBuilder);
    $queryBuilder->shouldReceive('whereNotNull')->with('user_ids')->andReturn($queryBuilder);

    $relation = new BelongsToArrayColumn(
        $eloquentBuilder,
        $child,
        'id',
        'user_ids',
        null,
        false
    );

    expect($relation)->toBeInstanceOf(BelongsToArrayColumn::class);
});

it('can create a belongs to array column relationship with string flag', function () {
    $queryBuilder = Mockery::mock(QueryBuilder::class);
    $eloquentBuilder = Mockery::mock(EloquentBuilder::class, [$queryBuilder]);
    $child = Mockery::mock(Company::class);
    $related = Mockery::mock(User::class);

    $eloquentBuilder->shouldReceive('getModel')->andReturn($related);
    $eloquentBuilder->shouldReceive('getQuery')->andReturn($queryBuilder);
    $queryBuilder->shouldReceive('when')->once()->andReturn($queryBuilder);
    $queryBuilder->shouldReceive('whereNotNull')->with('user_ids')->andReturn($queryBuilder);

    $relation = new BelongsToArrayColumn(
        $eloquentBuilder,
        $child,
        'id',
        'user_ids',
        null,
        true
    );

    expect($relation)->toBeInstanceOf(BelongsToArrayColumn::class);
});

it('gets owner key name correctly', function () {
    $queryBuilder = Mockery::mock(QueryBuilder::class);
    $eloquentBuilder = Mockery::mock(EloquentBuilder::class, [$queryBuilder]);
    $child = Mockery::mock(Company::class);
    $related = Mockery::mock(User::class);

    $eloquentBuilder->shouldReceive('getModel')->andReturn($related);
    $eloquentBuilder->shouldReceive('getQuery')->andReturn($queryBuilder);
    $queryBuilder->shouldReceive('when')->once()->andReturn($queryBuilder);
    $queryBuilder->shouldReceive('whereNotNull')->with('user_ids')->andReturn($queryBuilder);

    $relation = new BelongsToArrayColumn(
        $eloquentBuilder,
        $child,
        'owner_id',
        'user_ids',
        null,
        false
    );

    $ownerKey = $relation->getOwnerKeyName();

    expect($ownerKey)->toBe('user_ids');
});

it('adds constraints properly without string flag', function () {
    $queryBuilder = Mockery::mock(QueryBuilder::class);
    $eloquentBuilder = Mockery::mock(EloquentBuilder::class, [$queryBuilder]);
    $child = Mockery::mock(Company::class);
    $related = Mockery::mock(User::class);

    $eloquentBuilder->shouldReceive('getModel')->andReturn($related);
    $eloquentBuilder->shouldReceive('getQuery')->andReturn($queryBuilder);
    $child->shouldReceive('getAttribute')->with('owner_id')->andReturn(1);
    $queryBuilder->shouldReceive('when')->twice()->with(false, Mockery::type('Closure'), Mockery::type('Closure'));
    $queryBuilder->shouldReceive('whereNotNull')->with('user_ids')->twice();

    $relation = new BelongsToArrayColumn(
        $eloquentBuilder,
        $child,
        'owner_id',
        'user_ids',
        null,
        false
    );

    $relation->addConstraints();

    expect(true)->toBeTrue(); // If we reach here, constraints were added successfully
});

it('adds constraints properly with string flag', function () {
    $queryBuilder = Mockery::mock(QueryBuilder::class);
    $eloquentBuilder = Mockery::mock(EloquentBuilder::class, [$queryBuilder]);
    $child = Mockery::mock(Company::class);
    $related = Mockery::mock(User::class);

    $eloquentBuilder->shouldReceive('getModel')->andReturn($related);
    $eloquentBuilder->shouldReceive('getQuery')->andReturn($queryBuilder);
    $child->shouldReceive('getAttribute')->with('id')->andReturn(1);
    $queryBuilder->shouldReceive('when')->twice()->with(true, Mockery::type('Closure'), Mockery::type('Closure'));
    $queryBuilder->shouldReceive('whereNotNull')->with('user_ids')->twice();

    $relation = new BelongsToArrayColumn(
        $eloquentBuilder,
        $child,
        'id',
        'user_ids',
        null,
        true
    );

    $relation->addConstraints();

    expect(true)->toBeTrue(); // If we reach here, constraints were added successfully
});

it('adds eager constraints properly without string flag', function () {
    $queryBuilder = Mockery::mock(QueryBuilder::class);
    $eloquentBuilder = Mockery::mock(EloquentBuilder::class, [$queryBuilder]);
    $child = Mockery::mock(Company::class);
    $related = Mockery::mock(User::class);

    $eloquentBuilder->shouldReceive('getModel')->andReturn($related);
    $eloquentBuilder->shouldReceive('getQuery')->andReturn($queryBuilder);
    $queryBuilder->shouldReceive('when')->once()->andReturn($queryBuilder); // Constructor call
    $queryBuilder->shouldReceive('whereNotNull')->with('user_ids')->andReturn($queryBuilder);

    // Mock the where call to execute the closure
    $eloquentBuilder->shouldReceive('where')->once()->with(Mockery::type('Closure'))->andReturnUsing(function ($closure) use ($queryBuilder) {
        // Create a mock query for the closure to use
        $mockQuery = Mockery::mock(QueryBuilder::class);
        $mockQuery->shouldReceive('when')->twice()->with(false, Mockery::type('Closure'), Mockery::type('Closure'))->andReturnUsing(function ($condition, $trueCallback, $falseCallback) use ($mockQuery) {
            if (! $condition) {
                $falseCallback($mockQuery);
            } else {
                $trueCallback($mockQuery);
            }

            return $mockQuery;
        });
        $mockQuery->shouldReceive('orWhereJsonContains')->twice();

        // Execute the closure with our mock
        $closure($mockQuery);

        return $queryBuilder;
    });

    $relation = new BelongsToArrayColumn(
        $eloquentBuilder,
        $child,
        'id',
        'user_ids',
        null,
        false
    );

    $model1 = Mockery::mock(Company::class);
    $model1->shouldReceive('getAttribute')->with('id')->andReturn(1);

    $model2 = Mockery::mock(Company::class);
    $model2->shouldReceive('getAttribute')->with('id')->andReturn(2);

    $relation->addEagerConstraints([$model1, $model2]);

    expect(true)->toBeTrue(); // If we reach here, eager constraints were added successfully
});

it('matches models properly', function () {
    $queryBuilder = Mockery::mock(QueryBuilder::class);
    $eloquentBuilder = Mockery::mock(EloquentBuilder::class, [$queryBuilder]);
    $child = Mockery::mock(Company::class);
    $related = Mockery::mock(User::class);

    $eloquentBuilder->shouldReceive('getModel')->andReturn($related);
    $eloquentBuilder->shouldReceive('getQuery')->andReturn($queryBuilder);
    $queryBuilder->shouldReceive('when')->once()->andReturn($queryBuilder);
    $queryBuilder->shouldReceive('whereNotNull')->with('user_ids')->andReturn($queryBuilder);

    $relation = new BelongsToArrayColumn(
        $eloquentBuilder,
        $child,
        'id',
        'user_ids',
        null,
        false
    );

    $user1 = new User(['id' => 1, 'user_ids' => [1, 2]]);
    $user2 = new User(['id' => 2, 'user_ids' => [2, 3]]);
    $user3 = new User(['id' => 3, 'user_ids' => [4, 5]]);

    $company1 = new Company(['id' => 1]);
    $company2 = new Company(['id' => 2]);
    $company3 = new Company(['id' => 6]); // No user has 6 in their user_ids

    $results = new Collection([$user1, $user2, $user3]);
    $models = $relation->match([$company1, $company2, $company3], $results, 'users');

    expect($models[0]->users)->toHaveCount(1)
        ->and($models[0]->users->first()->id)->toBe(1)
        ->and($models[1]->users)->toHaveCount(2)
        ->and($models[1]->users->pluck('id')->toArray())->toBe([1, 2])
        ->and($models[2]->users)->toHaveCount(0);
});

it('gets results correctly', function () {
    $queryBuilder = Mockery::mock(QueryBuilder::class);
    $eloquentBuilder = Mockery::mock(EloquentBuilder::class, [$queryBuilder]);
    $child = Mockery::mock(Company::class);
    $related = Mockery::mock(User::class);

    $eloquentBuilder->shouldReceive('getModel')->andReturn($related);
    $eloquentBuilder->shouldReceive('getQuery')->andReturn($queryBuilder);
    $eloquentBuilder->shouldReceive('get')->andReturn(new Collection([]));
    $queryBuilder->shouldReceive('when')->once()->andReturn($queryBuilder);
    $queryBuilder->shouldReceive('whereNotNull')->with('user_ids')->andReturn($queryBuilder);

    $relation = new BelongsToArrayColumn(
        $eloquentBuilder,
        $child,
        'id',
        'user_ids',
        null,
        false
    );

    $results = $relation->getResults();

    expect($results)->toBeInstanceOf(Collection::class);
});
