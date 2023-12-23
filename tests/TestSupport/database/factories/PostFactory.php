<?php

namespace Salehhashemi\Repository\Tests\TestSupport\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Salehhashemi\Repository\Tests\TestSupport\Models\Category;
use Salehhashemi\Repository\Tests\TestSupport\Models\Comment;
use Salehhashemi\Repository\Tests\TestSupport\Models\Post;

class PostFactory extends Factory
{
    /**
     * @var class-string<Post>
     */
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'status' => $this->faker->randomElement(['draft', 'published']),
            'is_published' => false,
            'is_featured' => false,
        ];
    }

    public function hasComments(int $count): Factory
    {
        return $this->has(Comment::factory()->count($count), 'comments');
    }

    public function hasCategories(int $count): Factory
    {
        return $this->afterCreating(function (Post $post) use ($count) {
            $categories = Category::factory()->count($count)->create();
            $post->categories()->attach($categories);
        });
    }
}
