<?php

namespace Salehhashemi\Repository\Tests;

use Illuminate\Contracts\Pagination\Paginator;
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

    public function testFindOneReturnsModelInstance()
    {
        $post = Post::factory()->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $foundPost = $postRepo->findOne($post->id);

        $this->assertInstanceOf(Post::class, $foundPost);
        $this->assertEquals($post->id, $foundPost->id);
    }

    public function testFindOneReturnsNullIfNotFound()
    {
        $postRepository = new PostRepository();
        $nonExistentId = 99999; // This ID does not exist in the database

        $foundPost = $postRepository->findOne($nonExistentId);

        $this->assertNull($foundPost);
    }

    public function testFindOneOrFailReturnsModelInstance()
    {
        $post = Post::factory()->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $foundPost = $postRepo->findOneOrFail($post->id);

        $this->assertInstanceOf(Post::class, $foundPost);
        $this->assertEquals($post->id, $foundPost->id);
    }

    public function testFindOneOrFailThrowsExceptionIfNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        $postRepository = new PostRepository();
        $nonExistentId = 99999; // This ID does not exist in the database

        $postRepository->findOneOrFail($nonExistentId);
    }

    public function testFindAllRetrievesAllRecords()
    {
        Post::factory()->count(5)->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $posts = $postRepo->findAll();

        $this->assertCount(5, $posts);
        $this->assertInstanceOf(EloquentCollection::class, $posts);
    }

    public function testFindAllWithLimitOption()
    {
        Post::factory()->count(10)->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $posts = $postRepo->findAll(['limit' => 5]);

        $this->assertCount(5, $posts);
    }

    public function testFindAllWithOffsetAndLimitOptions()
    {
        Post::factory()->count(10)->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $posts = $postRepo->findAll(['offset' => 5, 'limit' => 4]);

        $this->assertCount(4, $posts);

        $expectedId = 5; // This depends on how IDs are assigned in your test database
        $firstPostAfterOffset = $posts->first();
        $this->assertEquals($expectedId, $firstPostAfterOffset->id);
    }

    public function testFindListRetrievesDefaultKeyValuePairs()
    {
        Post::factory()->count(3)->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $list = $postRepo->findList();

        $this->assertCount(3, $list);
        foreach ($list as $key => $value) {
            $this->assertIsNumeric($key); // Default key is 'id'
            $this->assertIsNumeric($value); // Default value is from 'getDisplayField'
        }
    }

    public function testFindListWithCustomKeyValuePairs()
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

    public function testInvalidPageSizeThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $postRepo->paginate(0);
    }

    public function testGetFilteredPosts()
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

        /** @var \Illuminate\Database\Eloquent\Collection $filteredPosts */
        $filteredPosts = $postRepo->search($filterOptions);

        $this->assertCount(1, $filteredPosts);
        $this->assertEquals('published', $filteredPosts->first()->status);
        $this->assertEquals(
            now()->subDay()->format('Y-m-d H:i:s'),
            $filteredPosts->first()->created_at->format('Y-m-d H:i:s')
        );
    }

    public function testGetCriteriaPosts()
    {
        Post::factory()->create(['is_featured' => 1]);
        Post::factory()->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $postRepo->addCriteria(new FeaturedPostCriteria());

        $filteredPosts = $postRepo->findAll();

        $this->assertCount(1, $filteredPosts);
    }

    public function testFindAllFeatured()
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

    public function testSearchVisible()
    {
        Post::factory()->count(6)->create(['status' => 'draft']);
        Post::factory()->count(4)->create(['status' => 'published']);

        $postRepo = $this->app->make(PostRepositoryInterface::class);
        $queryParams = ['status' => 'draft'];
        $paginatedPosts = $postRepo->searchVisible($queryParams, 5);

        $this->assertInstanceOf(Paginator::class, $paginatedPosts);
        $this->assertCount(5, $paginatedPosts->items());
    }

    public function testFindOnePublishedOrFail()
    {
        $publishedPost = Post::factory()->create(['is_published' => 1]);

        $postRepo = $this->app->make(PostRepositoryInterface::class);

        $foundPost = $postRepo->findOnePublishedOrFail($publishedPost->id);
        $this->assertInstanceOf(Post::class, $foundPost);
        $this->assertEquals(1, $foundPost->is_published);

        $this->expectException(ModelNotFoundException::class);
        $postRepo->findOnePublishedOrFail(999); // Non-existent ID
    }

    public function testWithComments()
    {
        $post = Post::factory()->hasComments(3)->create();

        $postRepo = $this->app->make(PostRepositoryInterface::class);
        $postRepo->withComments();
        $foundPost = $postRepo->findOne($post->id);

        $this->assertCount(3, $foundPost->comments);
    }

    public function testWithCategories()
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

    public function testLockForUpdateUsage()
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

    public function testSharedLockUsage()
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
