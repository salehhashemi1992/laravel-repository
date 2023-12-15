<?php

namespace Salehhashemi\Repository\Tests\TestSupport\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Salehhashemi\Repository\Tests\TestSupport\database\factories\PostFactory;

/**
 * @property int $id
 * @property string $title
 * @property string $content
 * @property string $status
 * @property bool $is_published
 * @property \Illuminate\Database\Eloquent\Collection|\Salehhashemi\Repository\Tests\TestSupport\Models\Comment[] $comments
 * @property \Illuminate\Database\Eloquent\Collection| \Salehhashemi\Repository\Tests\TestSupport\Models\Category[] $categories
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Post newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Post newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Post query()
 */
class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'status', 'is_published'];

    /**
     * {@inheritDoc}
     */
    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }

    /**
     * Get the comments for the post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * The categories that belong to the post.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}
