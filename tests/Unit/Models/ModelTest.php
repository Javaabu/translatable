<?php

namespace Javaabu\Translatable\Tests\Unit\Models;

use Javaabu\Translatable\Tests\TestCase;
use Javaabu\Translatable\Tests\TestSupport\Models\Article;
use PHPUnit\Framework\Attributes\Test;

class ModelTest extends TestCase
{
    #[Test]
    public function it_can_make_a_new_instance_of_the_model_even_if_the_languages_table_is_missing(): void
    {
        $article = new Article();

        $this->assertInstanceOf(Article::class, $article);
    }
}
