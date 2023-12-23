<?php

namespace Salehhashemi\Repository\Tests\TestSupport\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Salehhashemi\Repository\BaseEloquentRepository;
use Salehhashemi\Repository\Tests\TestSupport\Filters\PostFilter;
use Salehhashemi\Repository\Tests\TestSupport\Models\Post;
use Salehhashemi\Repository\Tests\TestSupport\Repositories\Criterias\FeaturedPostCriteria;
use Salehhashemi\Repository\Traits\Searchable;

/**
 * @method \Salehhashemi\Repository\Tests\TestSupport\Models\Post getModel()
 * @method \Salehhashemi\Repository\Tests\TestSupport\Models\Post|null findOne(int|string $primaryKey = null)
 * @method \Salehhashemi\Repository\Tests\TestSupport\Models\Post findOneOrFail(int|string $primaryKey = null)
 */
class PostRepository extends BaseEloquentRepository implements PostRepositoryInterface
{
    use Searchable;

    protected function getFilterManager(): PostFilter
    {
        $filterManager = new PostFilter();
        $filterManager->setQuery($this->getQuery());

        return $filterManager;
    }

    public function findAllFeatured(): EloquentCollection
    {
        $this->addCriteria(new FeaturedPostCriteria());

        return $this->findAll(['limit' => 20]);
    }

    public function searchVisible(array $queryParams, int $perPage): Paginator
    {
        $this->orderBy('id');
        $this->withCategories();

        return $this->search($queryParams, $perPage);
    }

    public function findOnePublishedOrFail(int $postId): Post
    {
        $this->applyConditions([
            'is_published' => 1,
        ]);

        return $this->findOneOrFail($postId);
    }

    public function withComments(): static
    {
        return $this->with(['comments']);
    }

    public function withCategories(): static
    {
        return $this->with(['categories']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelClass(): string
    {
        return Post::class;
    }
}
