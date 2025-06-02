<?php

declare(strict_types=1);

use MrPunyapal\LaravelExtendedRelationships\Tests\Models\Post;
use MrPunyapal\LaravelExtendedRelationships\Tests\Models\User;

it('debug query directly', function () {
    // Create test users
    User::create(['id' => 400, 'name' => 'Dave Admin', 'email' => 'dave@example.com']);
    User::create(['id' => 500, 'name' => 'Eve Manager', 'email' => 'eve@example.com']);

    // Create posts
    Post::create(['id' => 5000, 'title' => 'Echo Post', 'content' => 'Echo content', 'created_by' => 400, 'updated_by' => 500]);
    Post::create(['id' => 6000, 'title' => 'Foxtrot Post', 'content' => 'Foxtrot content', 'created_by' => 500, 'updated_by' => 400]);

    // Verify users exist
    $users = User::whereIn('id', [400, 500])->get();
    expect($users)->toHaveCount(2, 'Users should exist');

    // Verify posts exist
    $posts = Post::whereIn('id', [5000, 6000])->get();
    expect($posts)->toHaveCount(2, 'Posts should exist');

    // Direct query
    $directPosts = Post::where(function ($query) {
        $query->whereIn('created_by', [400, 500])
            ->orWhereIn('updated_by', [400, 500]);
    })->get();

    expect($directPosts)->toHaveCount(2, 'Direct query should find posts');
});
