<?php

namespace Javabu\Translatable\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Javaabu\Translatable\Models\Language;
use Javaabu\Translatable\Tests\TestCase;
use Javaabu\Translatable\Tests\TestSupport\Models\Article;
use Javaabu\Translatable\Tests\TestSupport\Models\Author;
use PHPUnit\Framework\Attributes\Test;

class IsJsonTranslatableCRUDTest extends TestCase {
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh');

        Language::create([
            'name'   => 'English',
            'code'   => 'en',
            'locale' => 'en',
            'flag'   => '🇬🇧',
            'is_rtl' => false,
            'active' => true,
        ]);
        Language::create([
            'name'   => 'Dhivehi',
            'code'   => 'dv',
            'locale' => 'dv',
            'flag'   => '🇲🇻',
            'is_rtl' => true,
            'active' => true,
        ]);
        Language::create([
            'name'   => 'Japanese',
            'code'   => 'jp',
            'locale' => 'jp',
            'flag'   => '🇯🇵',
            'is_rtl' => false,
            'active' => true,
        ]);

        Route::group(['middleware' => ['web', 'language']], function () {
            Route::get('/{language}/articles', function () {
                $articles = Article::all();

                return $articles;
            });

            Route::post('/{language}/articles', function () {
                $validated = request()->validate([
                    'title' => 'required',
                    'body' => 'required',
                    'slug' => 'required',
                    'author_id' => 'required|exists:authors,id',
                ]);
                $article = Article::create($validated);
                return $article;
            });

            Route::patch('/{language}/articles/{article}', function (Article $article) {
                dump(app()->getLocale());
                $validated = request()->validate([
                    'title' => '',
                    'body' => '',
                    'slug' => '',
                    'author_id' => 'exists:authors,id',
                ]);
                $article->update($validated);
            });
        });
    }

    #[Test]
    public function it_can_create_a_translation_for_articles()
    {
        self::markTestIncomplete();
        $author = Author::factory()->create();
        $article = Article::factory()->make();

        $this->post('/en/articles', [
            'title' => $article->title,
            'body' => $article->body,
            'slug' => $article->slug,
            'author_id' => $author->id,
        ]);

        $this->assertDatabaseHas('articles', [
            'title' => $article->title,
            'body' => $article->body,
        ]);

        $res = $this->patch('/dv/articles/' . $article->id, [
            'title' => 'ޓެސްޓެއް',
        ]);

        dump($res->getContent());

        $this->assertDatabaseHas('articles', [
            'title' => $article->title,
            'body' => $article->body,
            'translations' => [
                'dv' => [
                    'title' => 'ޓެސްޓެއް'
                ]
            ]
        ]);
    }
}
