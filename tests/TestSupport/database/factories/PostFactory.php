<?php

namespace Salehhashemi\Repository\Tests\TestSupport\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
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
            'is_published' => $this->faker->boolean,
        ];
    }
}
