<?php

namespace Javaabu\Translatable\Tests\TestSupport\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Javaabu\Translatable\Tests\TestSupport\Models\Article;
use Javaabu\Translatable\Tests\TestSupport\Models\Author;
use Javaabu\Translatable\Tests\TestSupport\Models\Post;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->title(),
            'slug' => $this->faker->slug(),
            'body' => $this->faker->paragraph(5),
            'lang' => $this->faker->languageCode(),
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
