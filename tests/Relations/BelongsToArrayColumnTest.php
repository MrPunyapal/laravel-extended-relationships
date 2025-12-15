<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Collection;
use MrPunyapal\LaravelExtendedRelationships\Relations\BelongsToArrayColumn;
use MrPunyapal\LaravelExtendedRelationships\Tests\Models\Company;
use MrPunyapal\LaravelExtendedRelationships\Tests\Models\User;

it('gets results correctly with actual database data', function (): void {
    // Create a company first
    $company = Company::create(['id' => 1, 'name' => 'Test Company']);

    // Create users that reference this company in their company_ids arrays
    User::create(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'company_ids' => [1, 3]]);
    User::create(['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'company_ids' => [1, 2]]);
    User::create(['id' => 3, 'name' => 'Bob Wilson', 'email' => 'bob@example.com', 'company_ids' => [2, 3]]);

    // Use the relationship to find users that have this company in their company_ids
    $relation = $company->belongsToArrayColumn(User::class, 'id', 'company_ids');
    $results = $relation->getResults();

    expect($results)->toBeInstanceOf(Collection::class)
        ->and($results)->toHaveCount(2)
        ->and($results->pluck('name')->sort()->values()->toArray())->toBe(['Jane Smith', 'John Doe']);
});

it('works with string flag for mixed data types', function (): void {
    $company = Company::create(['id' => 1, 'name' => 'String Company']);

    // Create users with string IDs in their company_ids arrays
    User::create(['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com', 'company_ids' => ['1', '2']]);
    User::create(['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com', 'company_ids' => [1, 3]]);

    $relation = $company->belongsToArrayColumn(User::class, 'id', 'company_ids', true);
    $results = $relation->getResults();

    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('Alice');
});

it('matches models with actual database data', function (): void {
    // Create companies first
    $company1 = Company::create(['id' => 1, 'name' => 'Company A']);
    $company2 = Company::create(['id' => 2, 'name' => 'Company B']);
    $company3 = Company::create(['id' => 3, 'name' => 'Company C']);

    // Create users that reference these companies in their company_ids arrays
    User::create(['id' => 1, 'name' => 'Alice Johnson', 'email' => 'alice@example.com', 'company_ids' => [1, 2]]);
    User::create(['id' => 2, 'name' => 'Charlie Brown', 'email' => 'charlie@example.com', 'company_ids' => [2, 3]]);
    User::create(['id' => 3, 'name' => 'Diana Prince', 'email' => 'diana@example.com', 'company_ids' => [999]]); // References non-existent company

    $relation = new BelongsToArrayColumn(
        User::query(),
        new Company,
        'id',
        'company_ids',
        null,
        false
    );

    // Get actual users from database
    $users = User::whereIn('id', [1, 2, 3])->get();

    $models = $relation->match([$company1, $company2, $company3], $users, 'employees');

    expect($models[0]->employees)->toHaveCount(1)
        ->and($models[0]->employees->pluck('name')->toArray())->toBe(['Alice Johnson'])
        ->and($models[1]->employees)->toHaveCount(2)
        ->and($models[1]->employees->pluck('name')->toArray())->toBe(['Alice Johnson', 'Charlie Brown'])
        ->and($models[2]->employees)->toHaveCount(1)
        ->and($models[2]->employees->pluck('name')->toArray())->toBe(['Charlie Brown']);
});

it('works with eager loading in database', function (): void {
    // Create companies
    $company1 = Company::create(['id' => 1, 'name' => 'Tech Corp']);
    $company2 = Company::create(['id' => 2, 'name' => 'Design Studio']);

    // Create users that reference these companies
    User::create(['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com', 'company_ids' => [1]]);
    User::create(['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com', 'company_ids' => [1, 2]]);
    User::create(['id' => 3, 'name' => 'Charlie', 'email' => 'charlie@example.com', 'company_ids' => [2]]);
    User::create(['id' => 4, 'name' => 'David', 'email' => 'david@example.com', 'company_ids' => [3]]); // Won't match

    // Use the proper way to test eager loading - through the actual relationship
    $companies = Company::with(['employees' => function ($query): void {
        $query->orderBy('name');
    }])->get();

    expect($companies)->toHaveCount(2);
    expect($companies[0]->employees)->toHaveCount(2) // Company 1 has Alice and Bob
        ->and($companies[0]->employees->pluck('name')->toArray())->toBe(['Alice', 'Bob']);
    expect($companies[1]->employees)->toHaveCount(2) // Company 2 has Bob and Charlie
        ->and($companies[1]->employees->pluck('name')->toArray())->toBe(['Bob', 'Charlie']);
});

it('handles empty arrays gracefully', function (): void {
    $company = Company::create(['id' => 1, 'name' => 'Empty Company']);

    // Create users with empty or null company_ids
    User::create(['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com', 'company_ids' => []]);
    User::create(['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com', 'company_ids' => null]);

    $relation = $company->belongsToArrayColumn(User::class, 'id', 'company_ids');
    $results = $relation->getResults();

    expect($results)->toHaveCount(0);
});

it('handles null json values gracefully', function (): void {
    $company = Company::create(['id' => 1, 'name' => 'Test Company']);

    // Create user with null company_ids (this should be excluded by whereNotNull constraint)
    User::create(['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com', 'company_ids' => null]);
    User::create(['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com', 'company_ids' => [1]]);

    $relation = $company->belongsToArrayColumn(User::class, 'id', 'company_ids');
    $results = $relation->getResults();

    expect($results)->toHaveCount(1)
        ->and($results->first()->name)->toBe('Bob');
});
