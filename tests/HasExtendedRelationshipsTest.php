<?php

declare(strict_types=1);

use MrPunyapal\LaravelExtendedRelationships\Relations\BelongsToArrayColumn;
use MrPunyapal\LaravelExtendedRelationships\Relations\BelongsToManyKeys;
use MrPunyapal\LaravelExtendedRelationships\Relations\HasManyArrayColumn;
use MrPunyapal\LaravelExtendedRelationships\Relations\HasManyKeys;
use MrPunyapal\LaravelExtendedRelationships\Tests\Models\Company;
use MrPunyapal\LaravelExtendedRelationships\Tests\Models\Post;
use MrPunyapal\LaravelExtendedRelationships\Tests\Models\User;

it('can create all relationship types through trait methods', function () {
    $post = new Post;
    $user = new User;
    $company = new Company;

    // Test that all relationship methods return correct instances
    expect($post->belongsToManyKeys(User::class, 'id', ['created_by' => 'creator']))->toBeInstanceOf(BelongsToManyKeys::class)
        ->and($user->hasManyKeys(Post::class, ['created_by' => 'created'], 'id'))->toBeInstanceOf(HasManyKeys::class)
        ->and($user->hasManyArrayColumn(Company::class, 'id', 'companies'))->toBeInstanceOf(HasManyArrayColumn::class)
        ->and($company->belongsToArrayColumn(User::class, 'id', 'user_ids'))->toBeInstanceOf(BelongsToArrayColumn::class)
        ->and($company->belongsToArrayColumn(User::class, 'id', 'user_ids', true))->toBeInstanceOf(BelongsToArrayColumn::class);
});

it('integrates all relationship types with database operations', function () {
    // Create base entities
    $author = User::create(['id' => 1000, 'name' => 'Jane Author', 'email' => 'jane@example.com', 'companies' => [1, 2], 'company_ids' => [1, 2]]);
    $editor = User::create(['id' => 2000, 'name' => 'John Editor', 'email' => 'john@example.com', 'companies' => [2, 3], 'company_ids' => [2, 3]]);

    Company::create(['id' => 1, 'name' => 'Tech Startup']);
    Company::create(['id' => 2, 'name' => 'Design Agency']);
    Company::create(['id' => 3, 'name' => 'Consulting Firm']);

    $post = Post::create([
        'id' => 5000,
        'title' => 'Integration Test Post',
        'content' => 'Testing all relationships',
        'created_by' => 1000,
        'updated_by' => 2000,
    ]);

    // Test BelongsToManyKeys - post to users
    $postAuditors = $post->belongsToManyKeys(User::class, 'id', ['created_by' => 'creator', 'updated_by' => 'updater']);
    $users = User::whereIn('id', [1000, 2000])->get();
    $matched = $postAuditors->match([$post], $users, 'auditors');

    expect($matched[0]->auditors->creator->name)->toBe('Jane Author')
        ->and($matched[0]->auditors->updater->name)->toBe('John Editor');

    // Test HasManyKeys - user to posts
    $userPosts = $author->hasManyKeys(Post::class, ['created_by' => 'created', 'updated_by' => 'updated'], 'id');
    $posts = Post::where('id', 5000)->get();
    $matchedUsers = $userPosts->match([$author, $editor], $posts, 'audited');

    expect($matchedUsers[0]->audited->created)->toHaveCount(1)
        ->and($matchedUsers[1]->audited->updated)->toHaveCount(1);

    // Test HasManyArrayColumn - user to companies
    $userCompanies = $author->hasManyArrayColumn(Company::class, 'id', 'companies');
    $companies = Company::whereIn('id', [1, 2, 3])->get();
    $matchedForCompanies = $userCompanies->matchMany([$author, $editor], $companies, 'workplaces');

    expect($matchedForCompanies[0]->workplaces)->toHaveCount(2)
        ->and($matchedForCompanies[1]->workplaces)->toHaveCount(2);

    // Test BelongsToArrayColumn - company to users (through company_ids)
    $company = Company::find(2);
    $companyEmployees = $company->belongsToArrayColumn(User::class, 'id', 'company_ids');
    $allUsers = User::whereIn('id', [1000, 2000])->get();
    $matchedEmployees = $companyEmployees->match([$company], $allUsers, 'employees');

    expect($matchedEmployees[0]->employees)->toHaveCount(2); // Both users reference company 2
});
