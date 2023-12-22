<?php

namespace Salehhashemi\Repository\Tests;

use Illuminate\Contracts\Pagination\Paginator;
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

    public function testPaginateWithSpecifiedPerPage()
    {
        Post::factory()->count(15)->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $perPage = 5;
        $paginatedResults = $postRepo->paginate($perPage);

        $this->assertInstanceOf(Paginator::class, $paginatedResults);
        $this->assertCount($perPage, $paginatedResults->items());
        $this->assertEquals(1, $paginatedResults->currentPage());
        $this->assertEquals(3, $paginatedResults->lastPage());
    }

    public function testPaginateWithDefaultPerPage()
    {
        Post::factory()->count(10)->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $defaultPerPage = 20;
        $paginatedResults = $postRepo->paginate();

        $this->assertInstanceOf(Paginator::class, $paginatedResults);
        $this->assertCount(min(10, $defaultPerPage), $paginatedResults->items());
    }
}
