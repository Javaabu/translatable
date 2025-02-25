<?php

namespace Feature;

use Javaabu\Translatable\Tests\TestCase;

class TranslatableSchemaTest extends TestCase
{
    /** @test */
    public function it_can_up_migrations()
    {
        $this->artisan('migrate:fresh');

        $posts = \Schema::getColumnListing('posts');
        $this->assertEquals([
            'id',
            'title',
            'slug',
            'body',
            'author_id',
            'translatable_parent_id',
            'lang',
            'created_at',
            'updated_at',
            'deleted_at',
        ], $posts);

        $articles = \Schema::getColumnListing('articles');
        $this->assertEquals([
            'id',
            'author_id',
            'title',
            'slug',
            'body',
            'translations',
            'lang',
            'created_at',
            'updated_at',
            'deleted_at',
        ], $articles);

        $authors = \Schema::getColumnListing('authors');
        $this->assertEquals([
            'id',
            'name',
            'created_at',
            'updated_at',
        ], $authors);
    }

    /** @test */
    public function it_can_down_migrations()
    {
        $this->artisan('migrate:fresh');
        $this->artisan('migrate:rollback');

        $posts = \Schema::getColumnListing('posts');
        $this->assertEmpty($posts);

        $articles = \Schema::getColumnListing('articles');
        $this->assertEmpty($articles);

        $authors = \Schema::getColumnListing('authors');
        $this->assertEmpty($authors);
    }
}
