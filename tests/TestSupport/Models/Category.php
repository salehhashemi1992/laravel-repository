<?php

namespace Salehhashemi\Repository\Tests\TestSupport\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Salehhashemi\Repository\Tests\TestSupport\database\factories\CategoryFactory;

/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Database\Eloquent\Collection|\Salehhashemi\Repository\Tests\TestSupport\Models\Post[] $posts
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * {@inheritDoc}
     */
    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }

    /**
     * The posts that belong to the category.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
}
