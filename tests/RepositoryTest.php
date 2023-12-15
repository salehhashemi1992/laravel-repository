<?php

namespace Salehhashemi\Repository\Tests;

use Salehhashemi\Repository\Tests\TestSupport\Models\Post;
use Salehhashemi\Repository\Tests\TestSupport\Repositories\PostRepository;
use Salehhashemi\Repository\Tests\TestSupport\Repositories\PostRepositoryInterface;

class RepositoryTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(
            PostRepositoryInterface::class,
            PostRepository::class
        );
    }

    public function testFindOneReturnsModelInstance()
    {
        $post = Post::factory()->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $foundPost = $postRepo->findOne($post->id);

        $this->assertInstanceOf(Post::class, $foundPost);
        $this->assertEquals($post->id, $foundPost->id);
    }
}
