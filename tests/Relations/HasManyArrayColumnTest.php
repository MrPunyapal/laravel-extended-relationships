<?php

declare(strict_types=1);

use MrPunyapal\LaravelExtendedRelationships\Relations\HasManyArrayColumn;
use MrPunyapal\LaravelExtendedRelationships\Tests\Models\Company;
use MrPunyapal\LaravelExtendedRelationships\Tests\Models\User;

it('works with actual database operations', function () {
    // Create companies
    Company::create(['id' => 1, 'name' => 'Tech Corp']);
    Company::create(['id' => 2, 'name' => 'Design Studio']);
    Company::create(['id' => 3, 'name' => 'Marketing Inc']);

    // Create users with company associations
    $user1 = User::create(['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com', 'companies' => [1, 2]]);
    $user2 = User::create(['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com', 'companies' => [2, 3]]);
    $user3 = User::create(['id' => 3, 'name' => 'Charlie', 'email' => 'charlie@example.com', 'companies' => [999]]); // Non-existent company

    $relation = new HasManyArrayColumn(
        Company::query(),
        new User,
        'id',
        'companies'
    );

    // Get actual companies from database
    $companies = Company::whereIn('id', [1, 2, 3])->get();

    $models = $relation->matchMany([$user1, $user2, $user3], $companies, 'workplaces');

    expect($models[0]->workplaces)->toHaveCount(2)
        ->and($models[0]->workplaces->pluck('name')->toArray())->toBe(['Tech Corp', 'Design Studio'])
        ->and($models[1]->workplaces)->toHaveCount(2)
        ->and($models[1]->workplaces->pluck('name')->toArray())->toBe(['Design Studio', 'Marketing Inc'])
        ->and($models[2]->workplaces)->toHaveCount(0); // No matching companies for ID 999
});

it('handles eager loading with database data', function () {
    // Create companies
    Company::create(['id' => 10, 'name' => 'Alpha Inc']);
    Company::create(['id' => 20, 'name' => 'Beta Corp']);
    Company::create(['id' => 30, 'name' => 'Gamma LLC']);

    // Create users with different company combinations
    User::create(['id' => 10, 'name' => 'Emma', 'email' => 'emma@example.com', 'companies' => [10, 20]]);
    User::create(['id' => 20, 'name' => 'Frank', 'email' => 'frank@example.com', 'companies' => [20]]);
    User::create(['id' => 30, 'name' => 'Grace', 'email' => 'grace@example.com', 'companies' => [10, 30]]);

    // Use actual eager loading through models
    $users = User::whereIn('id', [10, 20, 30])->get();

    $relation = new HasManyArrayColumn(
        Company::query(),
        new User,
        'id',
        'companies'
    );

    $relation->addEagerConstraints($users->all());
    $companies = $relation->getResults();

    // Should get all companies referenced in user arrays
    expect($companies)->toHaveCount(3)
        ->and($companies->pluck('name')->sort()->values()->toArray())->toBe(['Alpha Inc', 'Beta Corp', 'Gamma LLC']);
});

it('handles empty and null arrays correctly', function () {
    Company::create(['id' => 100, 'name' => 'Solo Corp']);

    // Create users with empty/null company arrays
    $user1 = User::create(['id' => 100, 'name' => 'Henry', 'email' => 'henry@example.com', 'companies' => []]);
    $user2 = User::create(['id' => 200, 'name' => 'Iris', 'email' => 'iris@example.com', 'companies' => null]);

    $relation = new HasManyArrayColumn(
        Company::query(),
        new User,
        'id',
        'companies'
    );

    $companies = Company::all();
    $models = $relation->matchMany([$user1, $user2], $companies, 'workplaces');

    expect($models[0]->workplaces)->toHaveCount(0)
        ->and($models[1]->workplaces)->toHaveCount(0);
});

it('handles parent key extraction correctly', function () {
    $user = new User(['companies' => [5, 10, 15]]);

    $relation = new HasManyArrayColumn(
        Company::query(),
        $user,
        'id',
        'companies'
    );

    $parentKeys = $relation->getParentKey();

    expect($parentKeys)->toBe([5, 10, 15]);
});

it('handles non-array parent key gracefully', function () {
    $user = new User(['companies' => 'not-an-array']);

    $relation = new HasManyArrayColumn(
        Company::query(),
        $user,
        'id',
        'companies'
    );

    $parentKeys = $relation->getParentKey();

    expect($parentKeys)->toBe([]);
});
