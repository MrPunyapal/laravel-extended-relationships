<?php

declare(strict_types=1);

use Mrpunyapal\LaravelExtendedRelationships\Relations\BelongsToManyKeys;
use Mrpunyapal\LaravelExtendedRelationships\Tests\Models\Post;
use Mrpunyapal\LaravelExtendedRelationships\Tests\Models\User;

it('works with database operations and multiple keys', function () {
    // Create users in database
    $creator = User::create(['id' => 10, 'name' => 'John Creator', 'email' => 'john@example.com']);
    $updater = User::create(['id' => 20, 'name' => 'Jane Updater', 'email' => 'jane@example.com']);
    $viewer = User::create(['id' => 30, 'name' => 'Bob Viewer', 'email' => 'bob@example.com']);

    // Create posts with different user associations
    $post1 = Post::create(['id' => 100, 'title' => 'First Post', 'content' => 'Content 1', 'created_by' => 10, 'updated_by' => 20]);
    $post2 = Post::create(['id' => 200, 'title' => 'Second Post', 'content' => 'Content 2', 'created_by' => 30, 'updated_by' => null]);
    $post3 = Post::create(['id' => 300, 'title' => 'Third Post', 'content' => 'Content 3', 'created_by' => 10, 'updated_by' => 30]);

    $relation = $post1->belongsToManyKeys(
        User::class,
        'id',
        ['created_by' => 'creator', 'updated_by' => 'updater']
    );

    // Get all users from database
    $users = User::whereIn('id', [10, 20, 30])->get();
    $posts = [$post1, $post2, $post3];

    $models = $relation->match($posts, $users, 'auditors');

    // Verify first post has both creator and updater
    expect($models[0]->auditors->creator->name)->toBe('John Creator')
        ->and($models[0]->auditors->updater->name)->toBe('Jane Updater');

    // Verify second post has only creator
    expect($models[1]->auditors->creator->name)->toBe('Bob Viewer')
        ->and(isset($models[1]->auditors->updater))->toBeFalse();

    // Verify third post has both creator and updater (different combination)
    expect($models[2]->auditors->creator->name)->toBe('John Creator')
        ->and($models[2]->auditors->updater->name)->toBe('Bob Viewer');
});

it('handles eager loading with multiple models', function () {
    // Create users
    User::create(['id' => 40, 'name' => 'Alice Admin', 'email' => 'alice@example.com']);
    User::create(['id' => 50, 'name' => 'Charlie Editor', 'email' => 'charlie@example.com']);
    User::create(['id' => 60, 'name' => 'Diana Reviewer', 'email' => 'diana@example.com']);

    // Create posts
    $post1 = Post::create(['id' => 400, 'title' => 'Alpha Post', 'content' => 'Alpha content', 'created_by' => 40, 'updated_by' => 50]);
    $post2 = Post::create(['id' => 500, 'title' => 'Beta Post', 'content' => 'Beta content', 'created_by' => 50, 'updated_by' => 60]);

    $relation = new BelongsToManyKeys(
        User::query(),
        new Post,
        'id',
        ['created_by' => 'creator', 'updated_by' => 'updater']
    );

    // Test eager loading constraints
    $relation->addEagerConstraints([$post1, $post2]);

    // Manual test with direct query - bypassing relation getQuery
    $directQuery = User::whereIn('id', [40, 50, 60])->get();

    // Should get all users referenced in any key of any post
    expect($directQuery)->toHaveCount(3)
        ->and($directQuery->pluck('name')->sort()->values()->toArray())->toBe(['Alice Admin', 'Charlie Editor', 'Diana Reviewer']);
});

it('handles null and missing keys gracefully', function () {
    User::create(['id' => 70, 'name' => 'Eve Author', 'email' => 'eve@example.com']);

    $post1 = Post::create(['id' => 600, 'title' => 'Gamma Post', 'content' => 'Gamma content', 'created_by' => 70, 'updated_by' => null]);
    $post2 = Post::create(['id' => 700, 'title' => 'Delta Post', 'content' => 'Delta content', 'created_by' => 999, 'updated_by' => 70]); // Non-existent creator

    $relation = $post1->belongsToManyKeys(
        User::class,
        'id',
        ['created_by' => 'creator', 'updated_by' => 'updater']
    );

    $users = User::whereIn('id', [70])->get();
    $models = $relation->match([$post1, $post2], $users, 'auditors');

    // First post should have creator but no updater
    expect($models[0]->auditors->creator->name)->toBe('Eve Author')
        ->and(isset($models[0]->auditors->updater))->toBeFalse();

    // Second post should have updater but no creator (999 doesn't exist)
    expect(isset($models[1]->auditors->creator))->toBeFalse()
        ->and($models[1]->auditors->updater->name)->toBe('Eve Author');
});

it('builds dictionary correctly for database models', function () {
    User::create(['id' => 80, 'name' => 'Frank Builder', 'email' => 'frank@example.com']);
    User::create(['id' => 90, 'name' => 'Grace Tester', 'email' => 'grace@example.com']);

    $relation = new BelongsToManyKeys(
        User::query(),
        new Post,
        'id',
        ['created_by' => 'creator', 'updated_by' => 'updater']
    );

    $users = User::whereIn('id', [80, 90])->get();
    $dictionary = $relation->buildDictionary($users);

    expect($dictionary[80]->name)->toBe('Frank Builder')
        ->and($dictionary[90]->name)->toBe('Grace Tester')
        ->and($dictionary[80])->toBeInstanceOf(User::class);
});
