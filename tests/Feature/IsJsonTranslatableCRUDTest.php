<?php

namespace Javaabu\Translatable\Tests\Feature;

use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Facades\Route;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Models\Language;
use Javaabu\Translatable\Tests\TestCase;
use Javaabu\Translatable\Tests\TestSupport\Models\Article;
use Javaabu\Translatable\Tests\TestSupport\Models\Author;
use PHPUnit\Framework\Attributes\Test;

class IsJsonTranslatableCRUDTest extends TestCase
{
    protected function setUp(): void
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
                return Article::all();
            });

            Route::post('/{language}/articles', function () {
                $validated = request()->validate([
                    'title'     => 'required',
                    'body'      => 'required',
                    'slug'      => 'required',
                    'author_id' => 'required|exists:authors,id',
                ]);

                return Article::create($validated);
            });

            Route::patch('/{language}/articles/{id}', function (Language $language, $articleId) {
                $article = Article::find($articleId);
                $validated = request()->validate([
                    'title'     => '',
                    'body'      => '',
                    'slug'      => '',
                    'author_id' => 'exists:authors,id',
                ]);

                return $article->update($validated);
            });
        });
    }

    #[Test]
    public function it_can_add_a_translation()
    {
        $author = Author::factory()->create();
        $article = Article::factory()->make();

        $response = $this->post('/en/articles', [
            'title'     => $article->title,
            'body'      => $article->body,
            'slug'      => $article->slug,
            'author_id' => $author->id,
        ]);
        $article->id = $response->json('id');

        $this->assertDatabaseHas('articles', [
            'title' => $article->title,
            'body'  => $article->body,
        ]);

        $this->patch('/dv/articles/' . $article->id, [
            'title' => 'Mee dhivehi title eh',
        ]);

        $this->assertDatabaseHas('articles', [
            'title'        => $article->title,
            'body'         => $article->body,
            'translations' => Json::encode([
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'lang'  => 'dv',
                ],
            ]),
        ]);

        // ensure slugs can't be added
        $this->patch('/dv/articles/' . $article->id, [
            'slug' => 'mee-dhivehi-slug-eh',
        ]);

        $this->assertDatabaseHas('articles', [
            'title'        => $article->title,
            'body'         => $article->body,
            'translations' => Json::encode([
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'lang'  => 'dv',
                ],
            ]),
        ]);
    }

    #[Test]
    public function it_can_edit_a_translation()
    {
        $article = Article::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);
        $article->title_dv = 'Mee dhivehi title eh';
        $article->save();

        $res = $this->patch('/dv/articles/' . $article->id, [
            'title' => 'Mee ehen dhivehi title eh',
        ]);

        Languages::setCurrentLocale('en');
        $this->assertDatabaseHas('articles', [
            'title'        => $article->title,
            'body'         => $article->body,
            'translations' => Json::encode([
                'dv' => [
                    'title' => 'Mee ehen dhivehi title eh',
                    'lang'  => 'dv',
                ],
            ]),
        ]);
    }
}
