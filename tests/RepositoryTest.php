<?php

namespace Salehhashemi\Repository\Tests;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Salehhashemi\Repository\Tests\TestSupport\Models\Category;
use Salehhashemi\Repository\Tests\TestSupport\Models\Post;
use Salehhashemi\Repository\Tests\TestSupport\Repositories\Criterias\FeaturedPostCriteria;
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

    public function test_find_one_returns_model_instance()
    {
        $post = Post::factory()->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $foundPost = $postRepo->findOne($post->id);

        $this->assertInstanceOf(Post::class, $foundPost);
        $this->assertEquals($post->id, $foundPost->id);
    }

    public function test_find_one_returns_null_if_not_found()
    {
        $postRepository = new PostRepository;
        $nonExistentId = 99999; // This ID does not exist in the database

        $foundPost = $postRepository->findOne($nonExistentId);

        $this->assertNull($foundPost);
    }

    public function test_find_one_or_fail_returns_model_instance()
    {
        $post = Post::factory()->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $foundPost = $postRepo->findOneOrFail($post->id);

        $this->assertInstanceOf(Post::class, $foundPost);
        $this->assertEquals($post->id, $foundPost->id);
    }

    public function test_find_one_or_fail_throws_exception_if_not_found()
    {
        $this->expectException(ModelNotFoundException::class);

        $postRepository = new PostRepository;
        $nonExistentId = 99999; // This ID does not exist in the database

        $postRepository->findOneOrFail($nonExistentId);
    }

    public function test_find_all_retrieves_all_records()
    {
        Post::factory()->count(5)->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $posts = $postRepo->findAll();

        $this->assertCount(5, $posts);
        $this->assertInstanceOf(EloquentCollection::class, $posts);
    }

    public function test_find_all_with_limit_option()
    {
        Post::factory()->count(10)->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $posts = $postRepo->findAll(['limit' => 5]);

        $this->assertCount(5, $posts);
    }

    public function test_find_all_with_offset_and_limit_options()
    {
        Post::factory()->count(10)->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $posts = $postRepo->findAll(['offset' => 5, 'limit' => 4]);

        $this->assertCount(4, $posts);

        $expectedId = 5; // This depends on how IDs are assigned in your test database
        $firstPostAfterOffset = $posts->first();
        $this->assertEquals($expectedId, $firstPostAfterOffset->id);
    }

    public function test_find_list_retrieves_default_key_value_pairs()
    {
        Post::factory()->count(3)->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $list = $postRepo->findList();

        $this->assertCount(3, $list);

        $expected = [
            '3' => 3,
            '2' => 2,
            '1' => 1,
        ];

        $this->assertSame($expected, $list->toArray());
    }

    public function test_find_list_with_custom_key_value_pairs()
    {
        Post::factory()->count(3)->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $list = $postRepo->findList('id', 'title');

        $this->assertCount(3, $list);
        foreach ($list as $key => $title) {
            $this->assertIsNumeric($key);
            $this->assertIsString($title);
        }
    }

    public function test_paginate_with_specified_per_page()
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

    public function test_paginate_with_default_per_page()
    {
        Post::factory()->count(10)->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $defaultPerPage = 20;
        $paginatedResults = $postRepo->paginate();

        $this->assertInstanceOf(Paginator::class, $paginatedResults);
        $this->assertCount(min(10, $defaultPerPage), $paginatedResults->items());
    }

    public function test_invalid_page_size_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $postRepo->paginate(0);
    }

    public function test_get_filtered_posts()
    {
        $category = Category::factory()->create();

        $post = Post::factory()->create([
            'title' => 'test',
            'status' => 'published',
            'created_at' => now()->subDay(),
        ]);

        $post->categories()->attach($category->id);

        Post::factory()->create(['status' => 'draft', 'created_at' => now()->subHour()]);

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        // Define filter options
        $filterOptions = [
            'title' => 'test',
            'status' => 'published',
            'orderBy' => 'created_at',
            'orderDirection' => 'ASC',
            'category_id' => $category->id,
        ];

        $postRepo->orderBy('created_at');

        /** @var Collection $filteredPosts */
        $filteredPosts = $postRepo->search($filterOptions);

        $this->assertCount(1, $filteredPosts);
        $this->assertEquals('published', $filteredPosts->first()->status);
    }

    public function test_filter_results_by_date()
    {
        Post::factory()->create([
            'title' => 'Post 03',
            'created_at' => now()->subDay(),
        ]);

        Post::factory()->create([
            'title' => 'Post 02',
            'created_at' => now()->subDays(2),
        ]);

        Post::factory()->create([
            'title' => 'Post 01',
            'created_at' => now()->subDays(3),
        ]);

        /** @var PostRepositoryInterface $postRepo */
        $postRepo = $this->app->make(PostRepositoryInterface::class);

        /** @var Collection $filteredPosts */
        $filteredPosts = $postRepo->search([
            'created_from' => now()->subDays(3)->toDateString(),
            'created_to' => now()->subDays(2)->toDateString(),
        ]);

        $this->assertSame(['Post 01', 'Post 02'], $filteredPosts->pluck('title')->toArray());
    }

    public function test_get_criteria_posts()
    {
        Post::factory()->create(['is_featured' => 1]);
        Post::factory()->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $postRepo->addCriteria(new FeaturedPostCriteria);

        $filteredPosts = $postRepo->findAll();

        $this->assertCount(1, $filteredPosts);
    }

    public function test_find_all_featured()
    {
        Post::factory()->count(3)->create(['is_featured' => 1]);
        Post::factory()->count(2)->create(['is_featured' => 0]);

        $postRepo = $this->app->make(PostRepositoryInterface::class);
        $featuredPosts = $postRepo->findAllFeatured();

        $this->assertCount(3, $featuredPosts);
        foreach ($featuredPosts as $post) {
            $this->assertEquals(1, $post->is_featured);
        }
    }

    public function test_search_visible()
    {
        Post::factory()->count(6)->create(['status' => 'draft']);
        Post::factory()->count(4)->create(['status' => 'published']);

        $postRepo = $this->app->make(PostRepositoryInterface::class);
        $queryParams = ['status' => 'draft'];
        $paginatedPosts = $postRepo->searchVisible($queryParams, 5);

        $this->assertInstanceOf(Paginator::class, $paginatedPosts);
        $this->assertCount(5, $paginatedPosts->items());
    }

    public function test_find_one_published_or_fail()
    {
        $publishedPost = Post::factory()->create(['is_published' => 1]);

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $foundPost = $postRepo->findOnePublishedOrFail($publishedPost->id);
        $this->assertInstanceOf(Post::class, $foundPost);
        $this->assertEquals(1, $foundPost->is_published);

        $this->expectException(ModelNotFoundException::class);
        $postRepo->findOnePublishedOrFail(999); // Non-existent ID
    }

    public function test_with_comments()
    {
        $post = Post::factory()->hasComments(3)->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);
        $postRepo->withComments();
        $foundPost = $postRepo->findOne($post->id);

        $this->assertCount(3, $foundPost->comments);
    }

    public function test_with_categories()
    {
        // Create posts and associate them with categories
        $post = Post::factory()->hasCategories(3)->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);
        $postRepo->withCategories();

        $retrievedPost = $postRepo->findOne($post->id);

        $this->assertTrue($retrievedPost->relationLoaded('categories'));
        $this->assertCount(3, $retrievedPost->categories);

        foreach ($retrievedPost->categories as $category) {
            $this->assertNotEmpty($category->name);
        }
    }

    public function test_lock_for_update_usage()
    {
        $post = Post::factory()->create();

        DB::beginTransaction();

        $postRepo = $this->app->make(PostRepositoryInterface::class);
        $postRepo->lockForUpdate();

        $lockedPost = $postRepo->findOne($post->id);
        $lockedPost->title = 'Updated Title';
        $lockedPost->save();

        DB::commit();

        $updatedPost = Post::query()->find($post->id);
        $this->assertEquals('Updated Title', $updatedPost->title);
    }

    public function test_shared_lock_usage()
    {
        $post = Post::factory()->create();

        DB::beginTransaction();

        $postRepo = $this->app->make(PostRepositoryInterface::class);
        $postRepo->sharedLock();

        $lockedPost = $postRepo->findOne($post->id);

        DB::commit();

        $this->assertInstanceOf(Post::class, $lockedPost);
        $this->assertEquals($post->id, $lockedPost->id);
    }
}
