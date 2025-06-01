<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Collection;
use Mrpunyapal\LaravelExtendedRelationships\Relations\HasManyKeys;
use Mrpunyapal\LaravelExtendedRelationships\Tests\Models\Post;
use Mrpunyapal\LaravelExtendedRelationships\Tests\Models\User;

it('can create a has many keys relationship', function () {
    $user = new User;

    $relation = $user->hasManyKeys(
        Post::class,
        ['created_by' => 'created', 'updated_by' => 'updated'],
        'id'
    );

    expect($relation)->toBeInstanceOf(HasManyKeys::class);
});

it('matches models properly with actual data', function () {
    $user = new User;
    $relation = $user->hasManyKeys(
        Post::class,
        ['created_by' => 'created', 'updated_by' => 'updated'],
        'id'
    );

    $post1 = new Post(['id' => 1, 'created_by' => 1, 'updated_by' => null]);
    $post2 = new Post(['id' => 2, 'created_by' => null, 'updated_by' => 1]);
    $post3 = new Post(['id' => 3, 'created_by' => 2, 'updated_by' => null]);

    $user1 = new User(['id' => 1]);
    $user2 = new User(['id' => 2]);

    $results = new Collection([$post1, $post2, $post3]);
    $models = $relation->match([$user1, $user2], $results, 'audited');

    expect($models[0]->audited->created->id)->toBe(1)
        ->and($models[0]->audited->updated->id)->toBe(2)
        ->and($models[1]->audited->created->id)->toBe(3)
        ->and(isset($models[1]->audited->updated))->toBeFalse();
});

it('builds dictionary correctly', function () {
    $user = new User;
    $relation = $user->hasManyKeys(
        Post::class,
        ['created_by' => 'created', 'updated_by' => 'updated'],
        'id'
    );

    $post1 = new Post(['created_by' => 1, 'updated_by' => 2]);
    $post2 = new Post(['created_by' => 3, 'updated_by' => 4]);

    $collection = new Collection([$post1, $post2]);
    $dictionary = $relation->buildDictionary($collection);

    expect($dictionary['created_by'][1])->toBe($post1)
        ->and($dictionary['updated_by'][2])->toBe($post1)
        ->and($dictionary['created_by'][3])->toBe($post2)
        ->and($dictionary['updated_by'][4])->toBe($post2);
});

it('gets parent key correctly', function () {
    $user = new User(['id' => 123]);

    $relation = $user->hasManyKeys(
        Post::class,
        ['created_by' => 'created', 'updated_by' => 'updated'],
        'id'
    );

    $key = $relation->getParentKey();
    expect($key)->toBe(123);
});

it('initializes relation on models', function () {
    $user = new User;
    $relation = $user->hasManyKeys(
        Post::class,
        ['created_by' => 'created', 'updated_by' => 'updated'],
        'id'
    );

    $model1 = new User;
    $model2 = new User;

    $result = $relation->initRelation([$model1, $model2], 'audited');

    expect($result)->toBe([$model1, $model2])
        ->and($model1->audited)->toBeInstanceOf(Collection::class)
        ->and($model2->audited)->toBeInstanceOf(Collection::class);
});
