<?php

namespace Salehhashemi\Repository\Tests\TestSupport\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Salehhashemi\Repository\Tests\TestSupport\database\factories\CommentFactory;

/**
 * The Comment model represents a comment made on a post.
 *
 * @property int $id
 * @property string $content
 * @property int $post_id
 * @property \Salehhashemi\Repository\Tests\TestSupport\Models\Post $post
 */
class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['content', 'post_id'];

    /**
     * {@inheritDoc}
     */
    protected static function newFactory(): CommentFactory
    {
        return CommentFactory::new();
    }

    /**
     * Get the post that the comment belongs to.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
