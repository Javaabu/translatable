<?php

namespace Javaabu\Translatable\Tests\TestSupport\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Javaabu\Translatable\Tests\TestSupport\Models\Author;
use Javaabu\Translatable\Tests\TestSupport\Models\Post;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title' => fake()->title(),
            'slug' => fake()->slug(),
            'body' => fake()->paragraph(5),
            'lang' => fake()->locale(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
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
