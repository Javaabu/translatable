<?php

namespace Javaabu\Translatable\Tests\Unit\JsonTranslatable;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Translatable\Tests\TestCase;
use Javaabu\Translatable\Tests\TestSupport\Models\Article;

class IsJsonTranslatableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_fields_ignored_for_translation()
    {
        $article = new Article();

        $this->assertEquals([
            'id',
            'translations',
            'lang',
            'created_at',
            'updated_at',
            'deleted_at',
        ], $article->getFieldsIgnoredForTranslation());
    }

    /** @test */
    public function it_can_get_translatable_fields()
    {
        $article = new Article();

        $this->assertEquals([
            'title',
            'body',
        ], $article->getTranslatables());
    }

    /** @test */
    public function it_can_get_non_translatable_fields()
    {
        $article = new Article();

        $this->assertEquals([
            'slug',
        ], $article->getNonTranslatables());
    }

    /** @test */
    public function it_can_get_non_translatable_pivots()
    {
        $article = new Article();

        $this->assertEquals([
            'author_id',
        ], $article->getNonTranslatablePivots());
    }

    /** @test */
    public function it_can_get_all_non_translatable_fields()
    {
        $article = new Article();

        $this->assertEquals([
            'slug',
            'id',
            'translations',
            'lang',
            'created_at',
            'updated_at',
            'deleted_at',
            'author_id',
        ], $article->getAllNonTranslatables());
    }

    /** @test */
    public function it_can_check_if_is_a_non_translatable_pivot()
    {
        $article = new Article();

        $this->assertFalse($article->isNonTranslatablePivot('id'));
        $this->assertTrue($article->isNonTranslatablePivot('author_id'));
    }

    /** @test */
    public function it_can_translate_field_via_translate_function()
    {
        $article = Article::factory()->withAuthor()->create([
            'lang' => 'en',
            'title' => 'This is an English title',
            'slug' => 'this-is-an-english-slug',
            'body' => 'This is an English body',
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug' => 'mee-dhivehi-slug-eh',
                    'body' => 'Mee dhivehi liyumeh',
                ],
                'jp' => [
                    'title' => 'Kore wa taitorudesu',
                    'slug' => 'kore-wa-namekujidesu',
                    'body' => 'Kore wa kijidesu',
                ]
            ]
        ]);

        $this->assertEquals('Mee dhivehi title eh', $article->translate('title', 'dv'));
        $this->assertEquals('Mee dhivehi liyumeh', $article->translate('body', 'dv'));

        $this->assertEquals('Kore wa taitorudesu', $article->translate('title', 'jp'));
        $this->assertEquals('Kore wa kijidesu', $article->translate('body', 'jp'));
    }

    /** @test */
    public function it_can_translate_field_via_translate_function_without_fallback()
    {
        $article = Article::factory()->withAuthor()->create([
            'lang' => 'en',
            'title' => 'This is an English title',
            'slug' => 'this-is-an-english-slug',
            'body' => 'This is an English body',
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug' => 'mee-dhivehi-slug-eh',
                    'body' => 'Mee dhivehi liyumeh',
                ],
            ]
        ]);

        $this->assertEquals('This is an English title', $article->translate('title', 'jp'));
        $this->assertEquals('this-is-an-english-slug', $article->translate('slug', 'jp'));
        $this->assertEquals('This is an English body', $article->translate('body', 'jp'));

        $this->assertNull($article->translate('title', 'jp', false));
        $this->assertNull($article->translate('slug', 'jp', false));
        $this->assertNull($article->translate('body', 'jp', false));
    }

    /** @test */
    public function it_can_translate_field_via_magic_method()
    {
        $article = Article::factory()->withAuthor()->create([
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug' => 'mee-dhivehi-slug-eh',
                    'body' => 'Mee dhivehi liyumeh',
                ],
                'jp' => [
                    'title' => 'Kore wa taitorudesu',
                    'slug' => 'kore-wa-namekujidesu',
                    'body' => 'Kore wa kijidesu',
                ]
            ]
        ]);

        $this->assertEquals('Mee dhivehi title eh', $article->title_dv);
        $this->assertEquals('Mee dhivehi liyumeh', $article->body_dv);

        // slugs should not be translated
        $this->assertNull($article->slug_dv);
    }

    /** @test */
    public function it_can_translate_field_via_locale_change()
    {
        $article = Article::factory()->withAuthor()->create([
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug' => 'mee-dhivehi-slug-eh',
                    'body' => 'Mee dhivehi liyumeh',
                ],
                'jp' => [
                    'title' => 'Kore wa taitorudesu',
                    'slug' => 'kore-wa-namekujidesu',
                    'body' => 'Kore wa kijidesu',
                ]
            ]
        ]);

        $tmp = app()->getLocale();
        app()->setLocale('dv');
        $this->assertEquals('Mee dhivehi title eh', $article->title);
        $this->assertEquals('Mee dhivehi liyumeh', $article->body);
        app()->setLocale($tmp);
    }

    /** @test */
    public function it_can_check_if_given_field_is_translatable()
    {
        $article = Article::factory()->withAuthor()->create([
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug' => 'mee-dhivehi-slug-eh',
                    'body' => 'Mee dhivehi liyumeh',
                ],
                'jp' => [
                    'title' => 'Kore wa taitorudesu',
                    'slug' => 'kore-wa-namekujidesu',
                    'body' => 'Kore wa kijidesu',
                ]
            ]
        ]);

        $this->assertTrue($article->isTranslatable('title'));
    }

    /** @test */
    public function it_can_clear_translations_for_one_locale()
    {
        $article = Article::factory()->withAuthor()->create([
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug' => 'mee-dhivehi-slug-eh',
                    'body' => 'Mee dhivehi liyumeh',
                ],
                'jp' => [
                    'title' => 'Kore wa taitorudesu',
                    'slug' => 'kore-wa-namekujidesu',
                    'body' => 'Kore wa kijidesu',
                ]
            ]
        ]);

        $article->clearTranslations('dv');

        // Ensure that dv is gone while jp is still there
        $this->assertEmpty($article->title_dv);
        $this->assertEquals('Kore wa taitorudesu', $article->title_jp);
    }

    /** @test */
    public function it_can_clear_translations_for_all_locales()
    {
        $article = Article::factory()->withAuthor()->create([
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug' => 'mee-dhivehi-slug-eh',
                    'body' => 'Mee dhivehi liyumeh',
                ],
                'jp' => [
                    'title' => 'Kore wa taitorudesu',
                    'slug' => 'kore-wa-namekujidesu',
                    'body' => 'Kore wa kijidesu',
                ]
            ]
        ]);

        $article->clearTranslations();

        $this->assertEmpty($article->title_dv);
        $this->assertEmpty($article->title_jp);
    }

    /** @test */
    public function it_can_check_if_any_translation_for_a_specific_locale()
    {
        $article = Article::factory()->withAuthor()->create([
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug' => 'mee-dhivehi-slug-eh',
                    'body' => 'Mee dhivehi liyumeh',
                ],
                'jp' => [
                    'title' => 'Kore wa taitorudesu',
                    'slug' => 'kore-wa-namekujidesu',
                    'body' => 'Kore wa kijidesu',
                ]
            ]
        ]);

        $this->assertFalse($article->hasTranslation('fr'));
        $this->assertTrue($article->hasTranslation('dv'));
    }

    /** @test */
    public function it_can_check_if_is_default_translation_locale()
    {
        $article = Article::factory()->withAuthor()->create([
            'lang' => 'en',
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug' => 'mee-dhivehi-slug-eh',
                    'body' => 'Mee dhivehi liyumeh',
                ],
                'jp' => [
                    'title' => 'Kore wa taitorudesu',
                    'slug' => 'kore-wa-namekujidesu',
                    'body' => 'Kore wa kijidesu',
                ]
            ]
        ]);

        $this->assertFalse($article->isDefaultTranslationLocale('fr'));
        $this->assertTrue($article->isDefaultTranslationLocale('en'));
    }

    /** @test */
    public function it_can_get_the_default_translation_locale()
    {
        $article = Article::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $this->assertFalse($article->isDefaultTranslationLocale('fr'));
        $this->assertTrue($article->isDefaultTranslationLocale('en'));
    }

    /** @test */
    public function it_can_get_allowed_translation_locales()
    {
        $article = Article::factory()->withAuthor()->create();

        $this->assertEquals(['en', 'dv', 'jp'], $article->getAllowedTranslationLocales());
    }

    /** @test */
    public function it_can_check_if_given_locale_is_allowed()
    {
        $article = Article::factory()->withAuthor()->create();

        $this->assertTrue($article->isAllowedTranslationLocale('en'));
        $this->assertTrue($article->isAllowedTranslationLocale('dv'));
        $this->assertFalse($article->isAllowedTranslationLocale('fr'));
    }
}
