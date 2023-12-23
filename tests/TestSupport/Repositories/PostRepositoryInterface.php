<?php

namespace Salehhashemi\Repository\Tests\TestSupport\Repositories;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Salehhashemi\Repository\Contracts\RepositoryInterface;
use Salehhashemi\Repository\Contracts\SearchableRepositoryInterface;
use Salehhashemi\Repository\Tests\TestSupport\Models\Post;

/**
 * @method \Salehhashemi\Repository\Tests\TestSupport\Models\Post|null findOne(int|string $primaryKey = null)
 * @method \Salehhashemi\Repository\Tests\TestSupport\Models\Post findOneOrFail(int|string $primaryKey = null)
 */
interface PostRepositoryInterface extends RepositoryInterface, SearchableRepositoryInterface
{
    public function findAllFeatured(): EloquentCollection;

    public function searchVisible(array $queryParams, int $perPage): Paginator;

    public function findOnePublishedOrFail(int $postId): Post;

    /**
     * @return $this
     */
    public function withComments(): static;

    /**
     * @return $this
     */
    public function withCategories(): static;
}
