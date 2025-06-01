<?php

declare(strict_types=1);

use Mrpunyapal\LaravelExtendedRelationships\Relations\BelongsToArrayColumn;
use Mrpunyapal\LaravelExtendedRelationships\Relations\BelongsToManyKeys;
use Mrpunyapal\LaravelExtendedRelationships\Relations\HasManyArrayColumn;
use Mrpunyapal\LaravelExtendedRelationships\Relations\HasManyKeys;
use Mrpunyapal\LaravelExtendedRelationships\Tests\Models\Company;
use Mrpunyapal\LaravelExtendedRelationships\Tests\Models\Post;
use Mrpunyapal\LaravelExtendedRelationships\Tests\Models\Tag;
use Mrpunyapal\LaravelExtendedRelationships\Tests\Models\User;

it('can create belongsToManyKeys relationship', function () {
    $post = new Post;

    $relation = $post->belongsToManyKeys(
        User::class,
        'id',
        ['created_by' => 'creator', 'updated_by' => 'updater']
    );

    expect($relation)->toBeInstanceOf(BelongsToManyKeys::class);
});

it('can create hasManyKeys relationship', function () {
    $user = new User;

    $relation = $user->hasManyKeys(
        Post::class,
        ['created_by' => 'created', 'updated_by' => 'updated'],
        'id'
    );

    expect($relation)->toBeInstanceOf(HasManyKeys::class);
});

it('can create hasManyArrayColumn relationship', function () {
    $user = new User;

    $relation = $user->hasManyArrayColumn(
        Company::class,
        'id',
        'companies'
    );

    expect($relation)->toBeInstanceOf(HasManyArrayColumn::class);
});

it('can create belongsToArrayColumn relationship', function () {
    $company = new Company;

    $relation = $company->belongsToArrayColumn(
        User::class,
        'id',
        'user_ids'
    );

    expect($relation)->toBeInstanceOf(BelongsToArrayColumn::class);
});

it('can create belongsToArrayColumn relationship with string flag', function () {
    $company = new Company;

    $relation = $company->belongsToArrayColumn(
        User::class,
        'id',
        'user_ids',
        true
    );

    expect($relation)->toBeInstanceOf(BelongsToArrayColumn::class);
});

it('creates related query correctly', function () {
    $user = new User;

    // Test that relatedNewQuery creates a proper query builder
    $reflection = new ReflectionClass($user);
    $method = $reflection->getMethod('relatedNewQuery');
    $method->setAccessible(true);

    $query = $method->invoke($user, User::class);

    expect($query)->toBeInstanceOf(\Illuminate\Database\Eloquent\Builder::class);
});

it('can define auditors relationship using belongsToManyKeys', function () {
    $post = new class extends Post
    {
        public function auditors()
        {
            return $this->belongsToManyKeys(
                User::class,
                'id',
                [
                    'created_by' => 'creator',
                    'updated_by' => 'updater',
                    'deleted_by' => 'deleter',
                ]
            );
        }
    };

    $relation = $post->auditors();

    expect($relation)->toBeInstanceOf(BelongsToManyKeys::class);
});

it('can define audited relationship using hasManyKeys', function () {
    $user = new class extends User
    {
        public function audited()
        {
            return $this->hasManyKeys(
                Post::class,
                [
                    'created_by' => 'created',
                    'updated_by' => 'updated',
                    'deleted_by' => 'deleted',
                ],
                'id'
            );
        }
    };

    $relation = $user->audited();

    expect($relation)->toBeInstanceOf(HasManyKeys::class);
});

it('can define companies relationship using hasManyArrayColumn', function () {
    $user = new class extends User
    {
        public function myCompanies()
        {
            return $this->hasManyArrayColumn(
                Company::class,
                'id',
                'companies'
            );
        }
    };

    $relation = $user->myCompanies();

    expect($relation)->toBeInstanceOf(HasManyArrayColumn::class);
});

it('can define founders relationship using belongsToArrayColumn', function () {
    $company = new class extends Company
    {
        public function founders()
        {
            return $this->belongsToArrayColumn(
                User::class,
                'id',
                'founder_ids'
            );
        }
    };

    $relation = $company->founders();

    expect($relation)->toBeInstanceOf(BelongsToArrayColumn::class);
});

it('can define tags relationship using hasManyArrayColumn', function () {
    $post = new class extends Post
    {
        public function tags()
        {
            return $this->hasManyArrayColumn(
                Tag::class,
                'id',
                'tag_ids'
            );
        }
    };

    $relation = $post->tags();

    expect($relation)->toBeInstanceOf(HasManyArrayColumn::class);
});

it('can define posts relationship using belongsToArrayColumn', function () {
    $tag = new class extends Tag
    {
        public function posts()
        {
            return $this->belongsToArrayColumn(
                Post::class,
                'id',
                'post_ids'
            );
        }
    };

    $relation = $tag->posts();

    expect($relation)->toBeInstanceOf(BelongsToArrayColumn::class);
});
