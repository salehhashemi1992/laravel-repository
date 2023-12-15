<?php

namespace Salehhashemi\Repository\Tests\TestSupport\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Salehhashemi\Repository\Tests\TestSupport\Models\Category;

class CategoryFactory extends Factory
{
    /**
     * @var class-string<Category>
     */
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
        ];
    }
}
