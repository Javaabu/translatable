<?php

namespace Javaabu\Translatable\Tests\TestSupport\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Javaabu\Translatable\Tests\TestSupport\Models\Article;
use Javaabu\Translatable\Tests\TestSupport\Models\Author;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'title' => fake()->title(),
            'slug' => fake()->slug(),
            'body' => fake()->paragraph(5),
            'lang' => fake()->locale()
        ];
    }

    public function withAuthor(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'author_id' => Author::factory(),
            ];
        });
    }
}
