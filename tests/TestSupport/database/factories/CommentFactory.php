<?php

namespace Salehhashemi\Repository\Tests\TestSupport\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Salehhashemi\Repository\Tests\TestSupport\Models\Comment;
use Salehhashemi\Repository\Tests\TestSupport\Models\Post;

class CommentFactory extends Factory
{
    /**
     * @var class-string<Comment>
     */
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'content' => $this->faker->text,
            'post_id' => Post::factory(),
        ];
    }
}
