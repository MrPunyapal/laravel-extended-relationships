<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Collection;
use Mrpunyapal\LaravelExtendedRelationships\Relations\BelongsToManyKeys;
use Mrpunyapal\LaravelExtendedRelationships\Tests\Models\Post;
use Mrpunyapal\LaravelExtendedRelationships\Tests\Models\User;

it('can create a belongs to many keys relationship', function () {
    $post = new Post;

    $relation = $post->belongsToManyKeys(
        User::class,
        'id',
        ['created_by' => 'creator', 'updated_by' => 'updater']
    );

    expect($relation)->toBeInstanceOf(BelongsToManyKeys::class);
});

it('matches models properly with actual data', function () {
    $post = new Post;
    $relation = $post->belongsToManyKeys(
        User::class,
        'id',
        ['created_by' => 'creator', 'updated_by' => 'updater']
    );

    $user1 = new User(['id' => 1]);
    $user2 = new User(['id' => 2]);
    $user3 = new User(['id' => 3]);

    $model1 = new Post(['created_by' => 1, 'updated_by' => 2]);
    $model2 = new Post(['created_by' => 3, 'updated_by' => null]);

    $results = new Collection([$user1, $user2, $user3]);
    $models = $relation->match([$model1, $model2], $results, 'auditors');

    expect($models[0]->auditors->creator->id)->toBe(1)
        ->and($models[0]->auditors->updater->id)->toBe(2)
        ->and($models[1]->auditors->creator->id)->toBe(3)
        ->and(isset($models[1]->auditors->updater))->toBeFalse();
});

it('builds dictionary correctly', function () {
    $post = new Post;
    $relation = $post->belongsToManyKeys(
        User::class,
        'id',
        ['created_by' => 'creator', 'updated_by' => 'updater']
    );

    $user1 = new User(['id' => 1]);
    $user2 = new User(['id' => 2]);

    $collection = new Collection([$user1, $user2]);
    $dictionary = $relation->buildDictionary($collection);

    expect($dictionary[1])->toBe($user1)
        ->and($dictionary[2])->toBe($user2);
});

it('gets parent key correctly', function () {
    $post = new Post(['created_by' => 123, 'updated_by' => 456]);

    $relation = $post->belongsToManyKeys(
        User::class,
        'id',
        ['created_by' => 'creator', 'updated_by' => 'updater']
    );

    $key = $relation->getParentKey('created_by');
    expect($key)->toBe(123);

    $key = $relation->getParentKey('updated_by');
    expect($key)->toBe(456);
});

it('initializes relation on models', function () {
    $post = new Post;
    $relation = $post->belongsToManyKeys(
        User::class,
        'id',
        ['created_by' => 'creator', 'updated_by' => 'updater']
    );

    $model1 = new Post;
    $model2 = new Post;

    $result = $relation->initRelation([$model1, $model2], 'auditors');

    expect($result)->toBe([$model1, $model2])
        ->and($model1->auditors)->toBeInstanceOf(Collection::class)
        ->and($model2->auditors)->toBeInstanceOf(Collection::class);
});
